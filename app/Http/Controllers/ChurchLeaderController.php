<?php

namespace App\Http\Controllers;

use App\Models\LeadershipRole;
use App\Models\Member;
use App\Services\MemberUserRoleService;
use Illuminate\Http\Request;

class ChurchLeaderController extends Controller
{
    public function index()
    {
        $roles = LeadershipRole::activeOrdered()
            ->with(['members' => fn ($q) => $q->with('department')->whereNull('parent_id')])
            ->get();

        $members = Member::with('department')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name', 'phone_number', 'department_id']);

        $allLeaders = Member::with(['leadershipRoles', 'department'])
            ->whereNull('parent_id')
            ->whereHas('leadershipRoles')
            ->orderBy('name')
            ->get();

        $stats = [
            'leaders' => $allLeaders->count(),
            'roles' => $roles->count(),
            'filled_roles' => $roles->filter(fn ($r) => $r->members->isNotEmpty())->count(),
            'assignments' => $allLeaders->sum(fn ($m) => $m->leadershipRoles->count()),
        ];

        return view('church-leaders.index', compact('roles', 'members', 'allLeaders', 'stats'));
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:leadership_roles,name',
            'name_sw' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $maxOrder = LeadershipRole::max('sort_order') ?? 0;

        LeadershipRole::create([
            'name' => $validated['name'],
            'name_sw' => $validated['name_sw'] ?? null,
            'description' => $validated['description'] ?? null,
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        return back()->with('success', __('New role has been added to the list.'));
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'leadership_role_id' => 'required|exists:leadership_roles,id',
            'assigned_at' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $member = Member::findOrFail($validated['member_id']);

        if ($member->isChild()) {
            return back()->with('error', __('You cannot assign a role to a child. Use the parent/guardian.'));
        }

        $exists = $member->leadershipRoles()
            ->where('leadership_role_id', $validated['leadership_role_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', __('This member already has this role.'));
        }

        $role = LeadershipRole::findOrFail($validated['leadership_role_id']);

        $member->leadershipRoles()->attach($validated['leadership_role_id'], [
            'assigned_at' => $validated['assigned_at'] ?? now()->toDateString(),
            'notes' => $validated['notes'] ?? null,
        ]);

        app(MemberUserRoleService::class)->syncLeadershipUserRole($member->fresh());

        return back()->with('success', __('Role has been assigned to the member.'));
    }

    public function unassign(Member $member, LeadershipRole $role)
    {
        $member->leadershipRoles()->detach($role->id);

        app(MemberUserRoleService::class)->syncLeadershipUserRole($member->fresh());

        return back()->with('success', __('Role has been removed.'));
    }
}
