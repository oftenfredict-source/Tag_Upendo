<?php

namespace App\Http\Controllers;

use App\Models\ChurchService;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CalendarController extends Controller
{
    public function index()
    {
        return view('calendar.index', [
            'eventTypes' => Event::types(),
            'serviceTypes' => Event::serviceTypes(),
            'members' => \App\Models\Member::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function feed(Request $request)
    {
        $start = Carbon::parse($request->input('start', now()->startOfMonth()));
        $end = Carbon::parse($request->input('end', now()->endOfMonth()));

        $calendarEvents = Event::where('start_at', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->where('end_at', '>=', $start)
                    ->orWhere(function ($q2) use ($start) {
                        $q2->whereNull('end_at')->where('start_at', '>=', $start);
                    });
            })
            ->get()
            ->map(fn (Event $event) => $event->toCalendarEntry());

        $attendanceServices = ChurchService::withCount('attendances')
            ->whereDate('service_date', '>=', $start->toDateString())
            ->whereDate('service_date', '<=', $end->toDateString())
            ->get()
            ->map(function (ChurchService $service) {
                return [
                    'id' => 'cs-' . $service->id,
                    'title' => '📋 ' . $service->displayName() . ' (' . $service->attendances_count . ')',
                    'start' => $service->service_date->format('Y-m-d'),
                    'allDay' => true,
                    'color' => '#28a745',
                    'url' => route('attendance.show', $service),
                    'extendedProps' => [
                        'source' => 'attendance',
                        'serviceId' => $service->id,
                    ],
                ];
            });

        return response()->json($calendarEvents->concat($attendanceServices)->values());
    }

    public function store(Request $request)
    {
        $validated = $request->validate(Event::validationRules());
        $validated['all_day'] = $request->boolean('all_day');

        if ($validated['all_day']) {
            $validated['start_at'] = Carbon::parse($validated['start_at'])->startOfDay();
            $validated['end_at'] = isset($validated['end_at'])
                ? Carbon::parse($validated['end_at'])->endOfDay()
                : Carbon::parse($validated['start_at'])->endOfDay();
        }

        $validated = $this->applyEventLeader($request, $validated);
        $event = Event::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tukio limeongezwa kwenye kalenda.',
                'event' => $event->toCalendarEntry(),
            ]);
        }

        return redirect()->route('calendar.index')->with('success', 'Tukio limeongezwa.');
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate(Event::validationRules($event->id));
        $validated['all_day'] = $request->boolean('all_day');

        if ($validated['all_day']) {
            $validated['start_at'] = Carbon::parse($validated['start_at'])->startOfDay();
            $validated['end_at'] = isset($validated['end_at'])
                ? Carbon::parse($validated['end_at'])->endOfDay()
                : Carbon::parse($validated['start_at'])->endOfDay();
        }

        $validated = $this->applyEventLeader($request, $validated);
        $event->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tukio limesasishwa.',
                'event' => $event->fresh()->toCalendarEntry(),
            ]);
        }

        return redirect()->route('calendar.index')->with('success', 'Tukio limesasishwa.');
    }

    public function destroy(Request $request, Event $event)
    {
        $event->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Tukio limefutwa.']);
        }

        return redirect()->route('calendar.index')->with('success', 'Tukio limefutwa.');
    }

    public function show(Event $event)
    {
        return response()->json([
            'id' => $event->id,
            'title' => $event->title,
            'leader' => $event->leader,
            'leader_member_id' => $event->leader_member_id,
            'event_type' => $event->event_type,
            'service_type' => $event->service_type,
            'start_at' => $event->start_at->format('Y-m-d\TH:i'),
            'end_at' => $event->end_at?->format('Y-m-d\TH:i'),
            'all_day' => $event->all_day,
            'location' => $event->location,
            'description' => $event->description,
            'church_service_id' => $event->church_service_id,
        ]);
    }

    public function startAttendance(Event $event)
    {
        if ($event->church_service_id) {
            return redirect()->route('attendance.collect', $event->church_service_id);
        }

        $service = ChurchService::create([
            'service_date' => $event->start_at->toDateString(),
            'service_type' => $event->service_type ?: 'Sunday Service',
            'title' => $event->title,
            'leader' => $event->leader,
            'notes' => $event->description,
        ]);

        $event->update(['church_service_id' => $service->id]);

        return redirect()
            ->route('attendance.collect', $service)
            ->with('success', 'Ibada imeunganishwa na kalenda. Weka mahudhurio sasa.');
    }

    protected function applyEventLeader(Request $request, array $validated): array
    {
        if ($request->filled('leader_member_id')) {
            $member = \App\Models\Member::find($request->input('leader_member_id'));
            $validated['leader_member_id'] = $request->input('leader_member_id');
            $validated['leader'] = $member?->name;
        } else {
            $validated['leader_member_id'] = null;
            $validated['leader'] = trim($validated['leader'] ?? '') ?: null;
        }

        return $validated;
    }
}
