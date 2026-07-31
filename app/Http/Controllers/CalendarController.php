<?php

namespace App\Http\Controllers;

use App\Models\ChurchService;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CalendarController extends Controller
{
    public function index()
    {
        return view('calendar.index', [
            'eventTypes' => Event::types(),
            'serviceTypes' => Event::serviceTypes(),
            'pastors' => \App\Models\Member::pastors()->orderBy('name')->get(['id', 'name']),
            'leaders' => \App\Models\Member::leaders()->with('leadershipRoles')->orderBy('name')->get(['id', 'name']),
            'elders' => \App\Models\Member::churchElders()->orderBy('name')->get(['id', 'name']),
            'members' => \App\Models\Member::adults()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function feed(Request $request)
    {
        $start = Carbon::parse($request->input('start', now()->startOfMonth()));
        $end = Carbon::parse($request->input('end', now()->endOfMonth()));

        $events = Event::where('start_at', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->where('end_at', '>=', $start)
                    ->orWhere(function ($q2) use ($start) {
                        $q2->whereNull('end_at')->where('start_at', '>=', $start);
                    });
            })
            ->orderBy('start_at')
            ->get();

        // One calendar card per service group (First+Second = one ibada)
        $grouped = $events
            ->groupBy(fn (Event $e) => $e->service_group_id ?: 'solo-' . $e->id)
            ->map(fn ($sessions) => Event::toGroupedCalendarEntry($sessions))
            ->values();

        // Skip attendance rows already linked to a calendar service (avoid duplicates)
        $linkedAttendanceIds = $events->pluck('church_service_id')->filter()->unique()->all();

        $attendanceServices = ChurchService::withCount('attendances')
            ->whereDate('service_date', '>=', $start->toDateString())
            ->whereDate('service_date', '<=', $end->toDateString())
            ->when($linkedAttendanceIds, fn ($q) => $q->whereNotIn('id', $linkedAttendanceIds))
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

        return response()->json($grouped->concat($attendanceServices)->values());
    }

    public function store(Request $request)
    {
        $validated = $request->validate(Event::validationRules());

        // Sunday Service only: one ibada with First & Second sessions
        if ($request->input('service_type') === 'Sunday Service') {
            $events = $this->createGroupedServices($request, $validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ibada ya Jumapili imeundwa (First & Second Service).',
                    'event' => $events[0]->toCalendarEntry(),
                ]);
            }

            return redirect()->route('calendar.index')->with('success', 'Ibada ya Jumapili imeundwa.');
        }

        $validated = $this->normalizeEventPayload($request, $validated);
        $validated['service_group_id'] = $validated['service_group_id'] ?? (string) Str::uuid();
        $event = Event::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ibada/tukio limeongezwa kwenye kalenda.',
                'event' => $event->toCalendarEntry(),
            ]);
        }

        return redirect()->route('calendar.index')->with('success', 'Ibada/tukio limeongezwa.');
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate(Event::validationRules($event->id));
        $validated = $this->normalizeEventPayload($request, $validated);
        $event->update($validated);

        // Keep sibling shared fields in sync when editing one session
        if ($event->service_group_id) {
            $shared = collect($validated)->only([
                'theme', 'location', 'choir', 'registered_members_count', 'guests_count',
                'scripture_readings', 'announcements', 'description',
                'preacher_member_id', 'coordinator_member_id', 'elder_member_id',
                'leader_member_id', 'leader',
            ])->all();

            Event::where('service_group_id', $event->service_group_id)
                ->where('id', '!=', $event->id)
                ->update($shared);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ibada/tukio limesasishwa.',
                'event' => $event->fresh()->toCalendarEntry(),
            ]);
        }

        return redirect()->route('calendar.index')->with('success', 'Ibada/tukio limesasishwa.');
    }

    public function destroy(Request $request, Event $event)
    {
        $event->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Ibada/tukio limefutwa.']);
        }

        return redirect()->route('calendar.index')->with('success', 'Ibada/tukio limefutwa.');
    }

    public function show(Event $event)
    {
        $event->load(['preacherMember.leadershipRoles']);

        return response()->json([
            'id' => $event->id,
            'title' => $event->title,
            'theme' => $event->theme,
            'leader' => $event->leader,
            'leader_member_id' => $event->leader_member_id,
            'preacher_member_id' => $event->preacher_member_id,
            'preacher_type' => $event->preacher_member_id
                ? optional($event->preacherMember)->preacherSourceType()
                : ($event->leader ? 'guest' : ''),
            'preacher_name' => $event->preacherMember?->name ?? $event->leader,
            'coordinator_member_id' => $event->coordinator_member_id,
            'elder_member_id' => $event->elder_member_id,
            'event_type' => $event->event_type,
            'service_type' => $event->service_type,
            'service_date' => $event->start_at->format('Y-m-d'),
            'start_time' => $event->all_day ? '' : $event->start_at->format('H:i'),
            'end_time' => $event->end_at && ! $event->all_day ? $event->end_at->format('H:i') : '',
            'start_at' => $event->start_at->format('Y-m-d\TH:i'),
            'end_at' => $event->end_at?->format('Y-m-d\TH:i'),
            'all_day' => $event->all_day,
            'location' => $event->location,
            'choir' => $event->choir,
            'registered_members_count' => $event->registered_members_count,
            'guests_count' => $event->guests_count,
            'scripture_readings' => $event->scripture_readings,
            'announcements' => $event->announcements,
            'description' => $event->description,
            'church_service_id' => $event->church_service_id,
            'service_group_id' => $event->service_group_id,
        ]);
    }

    public function startAttendance(Event $event)
    {
        if (! $event->canRecordAttendance()) {
            $message = __('Attendance can only be recorded during or after the service.');

            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()
                ->route('services.show', $event)
                ->with('error', $message);
        }

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

    protected function createGroupedServices(Request $request, array $validated): array
    {
        $date = Carbon::parse($request->input('service_date', now()->toDateString()))->toDateString();
        $firstStart = $request->input('first_start_time', '07:00');
        $firstEnd = $request->input('first_end_time', '09:00');
        $secondStart = $request->input('second_start_time', '10:00');
        $secondEnd = $request->input('second_end_time', '12:00');

        $base = $this->sharedServiceAttributes($request, $validated);
        $groupId = (string) Str::uuid();

        $sessions = [
            [
                'service_type' => 'First Service (Sunday)',
                'title' => 'First Service (Sunday)',
                'start_at' => Carbon::parse($date . ' ' . $firstStart),
                'end_at' => Carbon::parse($date . ' ' . $firstEnd),
            ],
            [
                'service_type' => 'Second Service (Sunday)',
                'title' => 'Second Service (Sunday)',
                'start_at' => Carbon::parse($date . ' ' . $secondStart),
                'end_at' => Carbon::parse($date . ' ' . $secondEnd),
            ],
        ];

        return DB::transaction(function () use ($sessions, $base, $groupId) {
            $created = [];
            foreach ($sessions as $session) {
                $created[] = Event::create(array_merge($base, $session, [
                    'service_group_id' => $groupId,
                    'event_type' => 'service',
                    'all_day' => false,
                ]));
            }

            return $created;
        });
    }

    protected function sharedServiceAttributes(Request $request, array $validated): array
    {
        [$preacherId, $leaderName] = $this->resolvePreacher($request);

        return [
            'theme' => $validated['theme'] ?? null,
            'location' => $validated['location'] ?? null,
            'choir' => $validated['choir'] ?? null,
            'registered_members_count' => $validated['registered_members_count'] ?? null,
            'guests_count' => $validated['guests_count'] ?? null,
            'scripture_readings' => $validated['scripture_readings'] ?? null,
            'announcements' => $validated['announcements'] ?? null,
            'description' => $validated['theme'] ?? ($validated['description'] ?? null),
            'preacher_member_id' => $preacherId,
            'leader_member_id' => $preacherId,
            'leader' => $leaderName,
            'coordinator_member_id' => $request->input('coordinator_member_id') ?: null,
            'elder_member_id' => $request->input('elder_member_id') ?: null,
        ];
    }

    /** @return array{0: ?int, 1: ?string} */
    protected function resolvePreacher(Request $request): array
    {
        if ($request->input('preacher_type') === 'guest') {
            $name = trim((string) $request->input('preacher_guest_name', ''));

            return [null, $name !== '' ? $name : null];
        }

        $preacherId = $request->input('preacher_member_id') ?: $request->input('leader_member_id');
        if (! $preacherId) {
            return [null, null];
        }

        return [(int) $preacherId, \App\Models\Member::find($preacherId)?->name];
    }

    protected function normalizeEventPayload(Request $request, array $validated): array
    {
        $validated['all_day'] = $request->boolean('all_day');
        $validated['event_type'] = $validated['event_type'] ?? 'service';

        if ($request->filled('service_date')) {
            $date = Carbon::parse($request->input('service_date'))->toDateString();
            $startTime = $request->input('start_time')
                ?: $request->input('first_start_time')
                ?: '09:00';
            $endTime = $request->input('end_time') ?: $request->input('first_end_time');

            if ($validated['all_day']) {
                $validated['start_at'] = Carbon::parse($date)->startOfDay();
                $validated['end_at'] = Carbon::parse($date)->endOfDay();
            } else {
                $validated['start_at'] = Carbon::parse($date . ' ' . $startTime);
                $validated['end_at'] = $endTime
                    ? Carbon::parse($date . ' ' . $endTime)
                    : null;
            }
        } elseif (! empty($validated['start_at'])) {
            if ($validated['all_day']) {
                $validated['start_at'] = Carbon::parse($validated['start_at'])->startOfDay();
                $validated['end_at'] = isset($validated['end_at'])
                    ? Carbon::parse($validated['end_at'])->endOfDay()
                    : Carbon::parse($validated['start_at'])->endOfDay();
            }
        }

        if (empty($validated['start_at'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'service_date' => ['Date is required.'],
            ]);
        }

        if (empty($validated['title'])) {
            $validated['title'] = $validated['service_type']
                ?: ($validated['theme'] ?: 'Church Service');
        }

        [$preacherId, $leaderName] = $this->resolvePreacher($request);
        $validated['preacher_member_id'] = $preacherId;
        $validated['leader_member_id'] = $preacherId;
        $validated['leader'] = $leaderName;

        $validated['coordinator_member_id'] = $request->input('coordinator_member_id') ?: null;
        $validated['elder_member_id'] = $request->input('elder_member_id') ?: null;

        unset(
            $validated['service_date'],
            $validated['start_time'],
            $validated['end_time'],
            $validated['has_two_services'],
            $validated['first_start_time'],
            $validated['first_end_time'],
            $validated['second_start_time'],
            $validated['second_end_time'],
            $validated['preacher_type'],
            $validated['preacher_guest_name']
        );

        return $validated;
    }
}
