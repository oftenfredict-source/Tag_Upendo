<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberRegistrationRequest;
use App\Models\RegistrationLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MemberRegistrationService
{
    /** @return array<string, mixed> */
    public function validationRules(Request $request, bool $allowExistingSpouse = true): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date|before:today',
            'birth_mkoa' => 'nullable|string|max:255',
            'birth_wilaya' => 'nullable|string|max:255',
            'residence_mkoa' => 'nullable|string|max:255',
            'residence_wilaya' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'marital_status' => 'nullable|in:single,married,widowed,divorced',
            'date_joined' => 'nullable|date',
            'is_baptized' => 'required|in:0,1',
            'baptism_date' => 'nullable|date',
            'occupation' => 'nullable|string|max:255',
            'member_type' => 'required|in:member,visitor,new_convert',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'department_id' => 'nullable|exists:departments,id',
            'spouse_is_member' => 'nullable|in:0,1',
            'spouse_mode' => 'nullable|in:new,existing',
            'existing_spouse_id' => 'nullable|exists:members,id',
            'spouse_name' => 'nullable|string|max:255',
            'spouse_phone_number' => 'nullable|string|max:20',
            'spouse_email' => 'nullable|email|max:255',
            'spouse_gender' => 'nullable|in:male,female',
            'spouse_date_of_birth' => 'nullable|date|before:today',
            'spouse_occupation' => 'nullable|string|max:255',
            'spouse_member_type' => 'nullable|in:member,visitor,new_convert',
            'spouse_department_id' => 'nullable|exists:departments,id',
            'spouse_is_baptized' => 'nullable|in:0,1',
            'spouse_baptism_date' => 'nullable|date',
            'spouse_date_joined' => 'nullable|date',
            'spouse_birth_mkoa' => 'nullable|string|max:255',
            'spouse_birth_wilaya' => 'nullable|string|max:255',
        ];

        if ($request->input('marital_status') === 'married' && $request->input('spouse_is_member') === '1') {
            if ($allowExistingSpouse) {
                $rules['spouse_mode'] = 'required|in:new,existing';

                if ($request->input('spouse_mode') === 'existing') {
                    $rules['existing_spouse_id'] = 'required|exists:members,id';
                } else {
                    $rules['spouse_name'] = 'required|string|max:255';
                    $rules['spouse_phone_number'] = 'required|string|max:20';
                    $rules['spouse_member_type'] = 'required|in:member,visitor,new_convert';
                    $rules['spouse_is_baptized'] = 'required|in:0,1';
                }
            } else {
                $rules['spouse_name'] = 'required|string|max:255';
                $rules['spouse_phone_number'] = 'required|string|max:20';
                $rules['spouse_member_type'] = 'required|in:member,visitor,new_convert';
                $rules['spouse_is_baptized'] = 'required|in:0,1';
            }
        }

        return $rules;
    }

    /** @return array{member: Member, accounts: array<int, array<string, string>>} */
    public function registerFromValidated(array $validated): array
    {
        $validated['is_baptized'] = (bool) $validated['is_baptized'];
        if (! $validated['is_baptized']) {
            $validated['baptism_date'] = null;
        }

        $memberData = collect($validated)->except([
            'spouse_is_member',
            'spouse_mode',
            'existing_spouse_id',
            'spouse_name',
            'spouse_phone_number',
            'spouse_email',
            'spouse_gender',
            'spouse_date_of_birth',
            'spouse_occupation',
            'spouse_member_type',
            'spouse_department_id',
            'spouse_is_baptized',
            'spouse_baptism_date',
            'spouse_date_joined',
            'spouse_birth_mkoa',
            'spouse_birth_wilaya',
        ])->all();

        $member = null;
        $newAccounts = [];

        DB::transaction(function () use (&$member, &$newAccounts, $memberData, $validated) {
            $member = Member::create($memberData);
            $member->update(['member_code' => MemberAccountService::generateMemberCode()]);

            $account = app(MemberAccountService::class)->provision($member->fresh());
            if ($account) {
                $newAccounts[] = $account;
            }

            if (
                ($validated['marital_status'] ?? null) === 'married'
                && ($validated['spouse_is_member'] ?? null) === '1'
            ) {
                $this->linkOrCreateSpouse($member, $validated);

                $spouse = $member->fresh()->spouse;
                if ($spouse && ! $spouse->member_code) {
                    $spouse->update(['member_code' => MemberAccountService::generateMemberCode()]);
                }
                if ($spouse) {
                    $spouseAccount = app(MemberAccountService::class)->provision($spouse->fresh());
                    if ($spouseAccount) {
                        $newAccounts[] = $spouseAccount;
                    }
                }
            }
        });

        return [
            'member' => $member->fresh(),
            'accounts' => $newAccounts,
        ];
    }

    public function createPendingRequest(RegistrationLink $link, array $validated): MemberRegistrationRequest
    {
        $validated['is_baptized'] = (bool) ($validated['is_baptized'] ?? false);
        if (! $validated['is_baptized']) {
            $validated['baptism_date'] = null;
        }

        if (($validated['marital_status'] ?? null) === 'married' && ($validated['spouse_is_member'] ?? null) === '1') {
            $validated['spouse_mode'] = 'new';
        }

        $request = MemberRegistrationRequest::create([
            'registration_link_id' => $link->id,
            'status' => 'pending',
            'payload' => $validated,
            'applicant_name' => $validated['name'],
            'applicant_phone' => $validated['phone_number'] ?? null,
            'applicant_email' => $validated['email'] ?? null,
        ]);

        $link->increment('uses_count');

        return $request;
    }

    /** @return array{member: Member, accounts: array<int, array<string, string>>} */
    public function approve(MemberRegistrationRequest $registrationRequest, User $reviewer): array
    {
        if (! $registrationRequest->isPending()) {
            throw new InvalidArgumentException(__('This registration request has already been processed.'));
        }

        $result = $this->registerFromValidated($registrationRequest->payload);

        $registrationRequest->update([
            'status' => 'approved',
            'member_id' => $result['member']->id,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        ActivityLogger::log(
            'member_registration.approve',
            __('Approved member registration for :name', ['name' => $registrationRequest->applicant_name]),
            $reviewer
        );

        return $result;
    }

    public function reject(MemberRegistrationRequest $registrationRequest, User $reviewer, ?string $reason = null): void
    {
        if (! $registrationRequest->isPending()) {
            throw new InvalidArgumentException(__('This registration request has already been processed.'));
        }

        $registrationRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        ActivityLogger::log(
            'member_registration.reject',
            __('Rejected member registration for :name', ['name' => $registrationRequest->applicant_name]),
            $reviewer
        );
    }

    protected function linkOrCreateSpouse(Member $member, array $validated): void
    {
        if (($validated['spouse_mode'] ?? '') === 'existing') {
            $spouse = Member::findOrFail($validated['existing_spouse_id']);

            if ($spouse->id === $member->id || $spouse->spouse_id) {
                return;
            }

            $member->update([
                'spouse_id' => $spouse->id,
                'marital_status' => 'married',
            ]);
            $spouse->update([
                'spouse_id' => $member->id,
                'marital_status' => 'married',
            ]);

            if (! $spouse->member_code) {
                $spouse->update(['member_code' => MemberAccountService::generateMemberCode()]);
            }

            return;
        }

        $spouseGender = $member->gender === 'male' ? 'female' : ($member->gender === 'female' ? 'male' : null);
        $spouseBaptized = (bool) ($validated['spouse_is_baptized'] ?? false);

        $spouse = Member::create([
            'name' => $validated['spouse_name'],
            'phone_number' => $validated['spouse_phone_number'],
            'email' => $validated['spouse_email'] ?? null,
            'gender' => $spouseGender,
            'date_of_birth' => $validated['spouse_date_of_birth'] ?? null,
            'occupation' => $validated['spouse_occupation'] ?? null,
            'member_type' => $validated['spouse_member_type'] ?? 'member',
            'department_id' => $validated['spouse_department_id'] ?? $member->department_id,
            'is_baptized' => $spouseBaptized,
            'baptism_date' => $spouseBaptized ? ($validated['spouse_baptism_date'] ?? null) : null,
            'date_joined' => $validated['spouse_date_joined'] ?? $member->date_joined,
            'marital_status' => 'married',
            'birth_mkoa' => $validated['spouse_birth_mkoa'] ?? null,
            'birth_wilaya' => $validated['spouse_birth_wilaya'] ?? null,
            'residence_mkoa' => $member->residence_mkoa,
            'residence_wilaya' => $member->residence_wilaya,
            'address' => $member->address,
            'emergency_contact_name' => $member->name,
            'emergency_contact_phone' => $member->phone_number,
        ]);

        $member->update([
            'spouse_id' => $spouse->id,
            'marital_status' => 'married',
        ]);
        $spouse->update(['spouse_id' => $member->id]);
    }
}
