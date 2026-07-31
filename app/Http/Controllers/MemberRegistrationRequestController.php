<?php

namespace App\Http\Controllers;

use App\Models\MemberRegistrationRequest;
use App\Services\MemberRegistrationService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MemberRegistrationRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $requests = MemberRegistrationRequest::with(['link', 'reviewer', 'member'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'pending' => MemberRegistrationRequest::where('status', 'pending')->count(),
            'approved' => MemberRegistrationRequest::where('status', 'approved')->count(),
            'rejected' => MemberRegistrationRequest::where('status', 'rejected')->count(),
        ];

        return view('member-registrations.index', compact('requests', 'status', 'counts'));
    }

    public function show(MemberRegistrationRequest $member_registration_request)
    {
        $member_registration_request->load(['link.creator', 'reviewer', 'member.spouse']);

        $payload = $member_registration_request->payload ?? [];
        $departments = \App\Models\Department::orderBy('name')->pluck('name', 'id');

        return view('member-registrations.show', [
            'registrationRequest' => $member_registration_request,
            'payload' => $payload,
            'departments' => $departments,
        ]);
    }

    public function approve(MemberRegistrationRequest $member_registration_request, MemberRegistrationService $service)
    {
        try {
            $result = $service->approve($member_registration_request, auth()->user());
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = __('Member :name has been registered and added to the member list.', [
            'name' => $result['member']->name,
        ]);

        return redirect()
            ->route('members.show', $result['member'])
            ->with('success', $message)
            ->with('new_member_accounts', $result['accounts']);
    }

    public function reject(Request $request, MemberRegistrationRequest $member_registration_request, MemberRegistrationService $service)
    {
        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        try {
            $service->reject($member_registration_request, auth()->user(), $validated['rejection_reason'] ?? null);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('member-registrations.index')
            ->with('success', __('Registration request rejected.'));
    }
}
