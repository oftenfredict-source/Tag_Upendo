<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\MemberRegistrationRequest;
use App\Models\RegistrationLink;
use App\Services\MemberRegistrationService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class PublicRegistrationController extends Controller
{
    public function create(string $token)
    {
        $link = RegistrationLink::findUsable($token);

        if (! $link) {
            return view('registration.invalid-link');
        }

        $departments = Department::orderBy('name')->get();
        $tzRegionNames = array_keys(config('tanzania_locations.regions'));
        sort($tzRegionNames);

        return view('registration.form', compact('link', 'departments', 'tzRegionNames', 'token'));
    }

    public function store(Request $request, string $token, MemberRegistrationService $service)
    {
        $link = RegistrationLink::findUsable($token);

        if (! $link) {
            return redirect()
                ->route('login')
                ->with('error', __('This registration link is invalid or has expired.'));
        }

        $request->merge(['spouse_mode' => 'new']);

        $validated = $request->validate($service->validationRules($request, allowExistingSpouse: false));

        $registrationRequest = $service->createPendingRequest($link, $validated);

        app(NotificationService::class)->notifyStaffOfRegistrationRequest($registrationRequest);

        return redirect()
            ->route('register.thanks', $token)
            ->with('registration_request_id', $registrationRequest->id);
    }

    public function thanks(string $token)
    {
        $link = RegistrationLink::where('token', $token)->first();

        return view('registration.thanks', compact('link', 'token'));
    }
}
