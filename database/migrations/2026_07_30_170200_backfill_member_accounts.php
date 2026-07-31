<?php

use App\Models\Member;
use App\Services\MemberAccountService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $service = app(MemberAccountService::class);

        Member::whereNull('parent_id')
            ->orderBy('id')
            ->each(function (Member $member) use ($service) {
                if (! $member->member_code) {
                    $member->update(['member_code' => MemberAccountService::generateMemberCode($member)]);
                }

                $service->provision($member->fresh());
            });
    }

    public function down(): void
    {
        // Accounts remain; no automatic rollback
    }
};
