<?php

namespace App\Services;

use App\Models\LeadershipRole;
use App\Models\Member;
use App\Models\User;

class MemberUserRoleService
{
    public function syncLeadershipUserRole(Member $member): void
    {
        $user = User::where('member_id', $member->id)->first();

        if (! $user || $user->isAdmin()) {
            return;
        }

        $member->loadMissing('leadershipRoles');

        if ($member->isPastor()) {
            if ($user->role !== 'pastor') {
                $user->update(['role' => 'pastor']);
            }

            return;
        }

        if ($member->isSecretary()) {
            if ($user->role !== 'secretary') {
                $user->update(['role' => 'secretary']);
            }

            return;
        }

        if (in_array($user->role, ['pastor', 'secretary'], true)) {
            $user->update(['role' => 'member']);
        }
    }

    /** @deprecated Use syncLeadershipUserRole() */
    public function syncPastorRole(Member $member): void
    {
        $this->syncLeadershipUserRole($member);
    }

    public function isPastorRole(LeadershipRole $role): bool
    {
        return str_contains($role->name, 'Pastor')
            || str_contains($role->name_sw ?? '', 'Mchungaji');
    }

    public function isSecretaryRole(LeadershipRole $role): bool
    {
        return str_contains($role->name, 'Secretary')
            || str_contains($role->name_sw ?? '', 'Katibu');
    }
}
