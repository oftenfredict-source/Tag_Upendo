<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\LeadershipRole;
use App\Models\Member;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberPortalController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $member = $user->member;

        if (! $member) {
            return view('member.dashboard', [
                'user' => $user,
                'member' => null,
                'tab' => 'overview',
                'portalRouteName' => $user->memberPortalRouteName(),
                'tithes' => collect(),
                'pledges' => collect(),
                'serviceRequests' => collect(),
                'contributionStats' => ['tithes_total' => 0, 'tithes_count' => 0, 'pledges_total' => 0, 'pledges_paid' => 0, 'pledges_remaining' => 0],
                'leaderRoles' => collect(),
                'announcements' => collect(),
            ]);
        }

        $tab = $request->query('tab', 'overview');
        $allowedTabs = ['overview', 'contributions', 'leaders', 'account'];
        if ($user->canSubmitOwnServiceRequests()) {
            $allowedTabs[] = 'requests';
        }
        if (! in_array($tab, $allowedTabs, true)) {
            $tab = 'overview';
        }

        $member->load(['department', 'spouse', 'leadershipRoles']);

        $tithes = $member->tithes()->latest('payment_date')->latest('id')->get();
        $pledges = $member->pledges()->with('payments')->latest('due_date')->get();
        $serviceRequests = $member->serviceRequests()->latest()->get();

        $contributionStats = [
            'tithes_total' => (float) $tithes->sum('amount'),
            'tithes_count' => $tithes->count(),
            'pledges_total' => (float) $pledges->sum('amount'),
            'pledges_paid' => (float) $pledges->sum('amount_paid'),
            'pledges_remaining' => (float) $pledges->sum(fn ($p) => $p->remainingAmount()),
        ];

        $leaderRoles = LeadershipRole::activeOrdered()
            ->with(['members' => fn ($q) => $q->with('department')->whereNull('parent_id')])
            ->get()
            ->filter(fn ($role) => $role->members->isNotEmpty());

        $announcements = Announcement::feedFor($user, 5);

        return view('member.dashboard', compact(
            'user',
            'member',
            'tab',
            'tithes',
            'pledges',
            'serviceRequests',
            'contributionStats',
            'leaderRoles',
            'announcements'
        ) + [
            'portalRouteName' => $user->memberPortalRouteName(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $member = $this->memberOrFail();

        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $member->update($validated);

        return $this->portalRedirect(['tab' => 'account'])
            ->with('success', __('Phone number updated successfully.'));
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->withInput()
                ->with('error', __('Current password is incorrect.'));
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        return $this->portalRedirect(['tab' => 'account'])
            ->with('success', __('Password changed successfully.'));
    }

    public function storeRequest(Request $request)
    {
        $member = $this->memberOrFail();

        $validated = $request->validate([
            'request_type' => 'required|in:' . implode(',', array_keys(ServiceRequest::types())),
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'preferred_date' => 'nullable|date|after_or_equal:today',
        ]);

        ServiceRequest::create([
            'member_id' => $member->id,
            'request_type' => $validated['request_type'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'preferred_date' => $validated['preferred_date'] ?? null,
            'status' => 'pending',
        ]);

        return $this->portalRedirect(['tab' => 'requests'])
            ->with('success', __('Your request has been submitted. The church office will contact you.'));
    }

    protected function memberOrFail(): Member
    {
        $member = auth()->user()->member;

        if (! $member) {
            abort(403, __('Your login is not linked to a member profile. Please contact the church office.'));
        }

        return $member;
    }

    protected function portalRedirect(array $params = [])
    {
        return redirect()->route(auth()->user()->memberPortalRouteName(), $params);
    }
}
