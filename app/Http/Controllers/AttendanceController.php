<?php

namespace App\Http\Controllers;

use App\Models\ChurchService;
use App\Models\Department;
use App\Models\Member;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $totalMembers = Member::count();

        $services = ChurchService::with('calendarEvent')
            ->withCount('attendances')
            ->latest('service_date')
            ->latest('id')
            ->paginate(15);

        return view('attendance.index', compact('services', 'totalMembers'));
    }

    public function create()
    {
        return view('attendance.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_date' => 'required|date',
            'service_type' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'leader' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $service = ChurchService::create($validated);

        return redirect()
            ->route('attendance.collect', $service)
            ->with('success', 'Ibada imeundwa. Weka alama za mahudhurio sasa.');
    }

    public function collect(ChurchService $service)
    {
        if (! $service->canRecordAttendance()) {
            return redirect()
                ->back()
                ->with('error', __('Attendance can only be recorded during or after the service.'));
        }

        $service->loadCount(['attendances']);

        $presentIds = $service->attendances()->pluck('member_id')->all();

        $members = Member::with('department')
            ->orderBy('name')
            ->get();

        $departments = Department::orderBy('name')->get();

        return view('attendance.collect', compact('service', 'members', 'presentIds', 'departments'));
    }

    public function saveCollect(Request $request, ChurchService $service)
    {
        if (! $service->canRecordAttendance()) {
            return redirect()
                ->route('attendance.show', $service)
                ->with('error', __('Attendance can only be recorded during or after the service.'));
        }

        $validated = $request->validate([
            'present' => 'nullable|array',
            'present.*' => 'exists:members,id',
        ]);

        $presentIds = $validated['present'] ?? [];

        $service->attendances()->delete();

        foreach ($presentIds as $memberId) {
            $service->attendances()->create([
                'member_id' => $memberId,
                'status' => 'present',
            ]);
        }

        return redirect()
            ->route('attendance.show', $service)
            ->with('success', count($presentIds) . ' wanachama wamewekwa kama waliohudhuria.');
    }

    public function show(ChurchService $service)
    {
        $service->load(['calendarEvent']);
        $service->loadCount(['attendances']);

        $presentMembers = Member::whereIn(
            'id',
            $service->attendances()->pluck('member_id')
        )->with('department')->orderBy('name')->get();

        $totalMembers = Member::count();
        $absentCount = max(0, $totalMembers - $service->attendances_count);

        return view('attendance.show', compact('service', 'presentMembers', 'totalMembers', 'absentCount'));
    }

    public function destroy(ChurchService $service)
    {
        $name = $service->displayName();
        $service->delete();

        return redirect()
            ->route('attendance.index')
            ->with('success', "Rekodi ya ibada ({$name}) imefutwa.");
    }
}
