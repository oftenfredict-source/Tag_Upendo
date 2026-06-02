<?php

namespace App\Http\Controllers;

use App\Models\LeadershipRole;
use App\Models\Member;
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
            return back()->with('error', 'Huwezi kuweka jukumu kwa mtoto. Tumia mzazi/mlezi.');
        }

        $exists = $member->leadershipRoles()
            ->where('leadership_role_id', $validated['leadership_role_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Mwanachama huyu tayari ana jukumu hili.');
        }

        $member->leadershipRoles()->attach($validated['leadership_role_id'], [
            'assigned_at' => $validated['assigned_at'] ?? now()->toDateString(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Jukumu limewekwa kwa mwanachama.');
    }

    public function unassign(Member $member, LeadershipRole $role)
    {
        $member->leadershipRoles()->detach($role->id);

        return back()->with('success', 'Jukumu limeondolewa.');
    }

}
