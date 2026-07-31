<?php

namespace App\Http\Controllers;

use App\Models\MemberRegistrationRequest;
use App\Models\RegistrationLink;
use Illuminate\Http\Request;

class RegistrationLinkController extends Controller
{
    public function index()
    {
        $activeRegistrationLink = RegistrationLink::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->first();

        $recentLinks = RegistrationLink::with('creator')
            ->latest()
            ->take(10)
            ->get();

        $pendingRegistrations = auth()->user()->canManageMemberRegistrations()
            ? MemberRegistrationRequest::where('status', 'pending')->count()
            : 0;

        return view('members.registration-link', compact(
            'activeRegistrationLink',
            'recentLinks',
            'pendingRegistrations'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date|after:now',
        ]);

        RegistrationLink::where('is_active', true)->update(['is_active' => false]);

        $link = RegistrationLink::create([
            'created_by' => auth()->id(),
            'label' => $validated['label'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'url' => $link->publicUrl(),
                'token' => $link->token,
            ]);
        }

        return redirect()
            ->route('members.registration-link')
            ->with('success', __('Registration link created. Share it with people who want to join the church.'));
    }

    public function toggle(RegistrationLink $registrationLink)
    {
        $registrationLink->update([
            'is_active' => ! $registrationLink->is_active,
        ]);

        return redirect()
            ->route('members.registration-link')
            ->with('success', $registrationLink->is_active
                ? __('Registration link activated.')
                : __('Registration link deactivated.'));
    }
}
