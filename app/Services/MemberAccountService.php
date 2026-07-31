<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MemberAccountService
{
    public static function generateMemberCode(): string
    {
        return DB::transaction(function () {
            $year = (int) now()->format('Y');
            $maxSequence = 0;

            Member::whereNotNull('member_code')
                ->where('member_code', 'like', 'TU%-' . $year)
                ->lockForUpdate()
                ->pluck('member_code')
                ->each(function (string $code) use (&$maxSequence, $year) {
                    if (preg_match('/^TU(\d+)-' . preg_quote((string) $year, '/') . '$/', $code, $matches)) {
                        $maxSequence = max($maxSequence, (int) $matches[1]);
                    }
                });

            $next = $maxSequence + 1;
            $sequence = $next <= 999
                ? str_pad((string) $next, 3, '0', STR_PAD_LEFT)
                : (string) $next;

            return "TU{$sequence}-{$year}";
        });
    }

    public static function extractLastName(string $fullName): string
    {
        $parts = preg_split('/\s+/u', trim($fullName), -1, PREG_SPLIT_NO_EMPTY);

        return $parts ? (string) end($parts) : trim($fullName);
    }

    public static function defaultPassword(string $fullName): string
    {
        return mb_strtoupper(self::extractLastName($fullName), 'UTF-8');
    }

    /** @return array{name: string, member_code: string, password: string}|null */
    public function provision(Member $member): ?array
    {
        if ($member->parent_id) {
            return null;
        }

        if (! $member->member_code) {
            $member->update(['member_code' => self::generateMemberCode()]);
            $member->refresh();
        }

        if (User::where('member_id', $member->id)->exists()) {
            return null;
        }

        $plainPassword = self::defaultPassword($member->name);
        $memberCode = $member->member_code;

        User::create([
            'name' => $member->name,
            'username' => $memberCode,
            'email' => strtolower(str_replace('-', '', $memberCode)) . '@members.tagupendo.local',
            'password' => Hash::make($plainPassword),
            'role' => 'member',
            'member_id' => $member->id,
        ]);

        $account = [
            'name' => $member->name,
            'member_code' => $memberCode,
            'password' => $plainPassword,
        ];

        $this->sendWelcomeSms($member, $account);

        return $account;
    }

    /** @param array{name: string, member_code: string, password: string} $account */
    public function sendWelcomeSms(Member $member, array $account): void
    {
        if (! filled($member->phone_number)) {
            return;
        }

        $churchName = Setting::get('church_name', 'TAG Upendo');
        $message = __('Welcome SMS message', [
            'name' => self::extractFirstName($member->name),
            'church' => $churchName,
            'username' => $account['member_code'],
            'password' => $account['password'],
        ]);

        try {
            $result = app(SmsService::class)->sendSingle($member->phone_number, $message);

            if (! ($result['success'] ?? false)) {
                Log::warning('[MemberAccountService] Welcome SMS failed', [
                    'member_id' => $member->id,
                    'phone' => $member->phone_number,
                    'message' => $result['message'] ?? 'Unknown error',
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[MemberAccountService] Welcome SMS error', [
                'member_id' => $member->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected static function extractFirstName(string $fullName): string
    {
        $parts = preg_split('/\s+/u', trim($fullName), -1, PREG_SPLIT_NO_EMPTY);

        return $parts ? (string) $parts[0] : trim($fullName);
    }
}
