<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Services\MemberUserRoleService;
use Illuminate\Console\Command;

class SyncPastorUserRoles extends Command
{
    protected $signature = 'users:sync-pastor-roles';

    protected $description = 'Sync user login roles for members assigned Pastor leadership roles';

    public function handle(MemberUserRoleService $roleService): int
    {
        $members = Member::whereNull('parent_id')
            ->whereHas('user')
            ->with(['user', 'leadershipRoles'])
            ->get();

        $updated = 0;

        foreach ($members as $member) {
            $before = $member->user->role;
            $roleService->syncLeadershipUserRole($member);
            $member->user->refresh();

            if ($member->user->role !== $before) {
                $updated++;
                $this->line("{$member->name}: {$before} → {$member->user->role}");
            }
        }

        $this->info("Synced {$updated} user role(s).");

        return self::SUCCESS;
    }
}
