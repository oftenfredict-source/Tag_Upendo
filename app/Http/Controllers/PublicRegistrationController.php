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
    public function landing()
    {
        $link = RegistrationLink::activeLink();

        if ($link) {
            return redirect()->route('register.show', $link->publicCode());
        }

        return view('registration.invalid-link');
    }

    public function create(string $code)
    {
        $link = RegistrationLink::findUsable($code);

        if (! $link) {
            return view('registration.invalid-link');
        }

        $departments = Department::orderBy('name')->get();
        $tzRegionNames = array_keys(config('tanzania_locations.regions'));
        sort($tzRegionNames);

        return view('registration.form', [
            'link' => $link,
            'departments' => $departments,
            'tzRegionNames' => $tzRegionNames,
            'code' => $link->publicCode(),
        ]);
    }

    public function store(Request $request, string $code, MemberRegistrationService $service)
    {
        $link = RegistrationLink::findUsable($code);

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
            ->route('register.thanks', $link->publicCode())
            ->with('registration_request_id', $registrationRequest->id);
    }

    public function thanks(string $code)
    {
        $link = RegistrationLink::findByPublicCode($code);

        return view('registration.thanks', compact('link', 'code'));
    }
}
