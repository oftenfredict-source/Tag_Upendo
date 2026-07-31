<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $base = Event::query()->where('event_type', 'service');

        if ($search = $request->input('search')) {
            $base->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('theme', 'like', "%{$search}%")
                    ->orWhere('service_type', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('choir', 'like', "%{$search}%")
                    ->orWhere('leader', 'like', "%{$search}%");
            });
        }

        if ($request->filled('from')) {
            $base->whereDate('start_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $base->whereDate('start_at', '<=', $request->to);
        }

        // One row per service group (ibada moja)
        $groupIds = (clone $base)
            ->select('service_group_id', DB::raw('MIN(start_at) as group_start'))
            ->whereNotNull('service_group_id')
            ->groupBy('service_group_id')
            ->orderByDesc('group_start')
            ->paginate(15)
            ->withQueryString();

        $groups = $groupIds->getCollection()->map(function ($row) {
            $sessions = Event::with(['preacherMember', 'coordinatorMember', 'elderMember', 'leaderMember'])
                ->where('service_group_id', $row->service_group_id)
                ->orderBy('start_at')
                ->get();

            $primary = $sessions->first();

            return (object) [
                'group_id' => $row->service_group_id,
                'primary' => $primary,
                'sessions' => $sessions,
                'session_count' => $sessions->count(),
                'date' => $primary?->start_at,
                'theme' => $primary?->theme,
                'location' => $primary?->location,
                'preacher' => $primary?->preacherMember?->name
                    ?? $primary?->leaderMember?->name
                    ?? $primary?->leader,
                'status' => Event::groupComputedStatus($sessions),
                'has_first' => $sessions->contains(fn ($s) => $s->service_type === 'First Service (Sunday)'),
                'has_second' => $sessions->contains(fn ($s) => $s->service_type === 'Second Service (Sunday)'),
            ];
        });

        $groupIds->setCollection($groups);

        $stats = [
            'total' => Event::where('event_type', 'service')->whereNotNull('service_group_id')->distinct('service_group_id')->count('service_group_id'),
            'this_month' => Event::where('event_type', 'service')
                ->whereNotNull('service_group_id')
                ->whereMonth('start_at', now()->month)
                ->whereYear('start_at', now()->year)
                ->distinct('service_group_id')
                ->count('service_group_id'),
            'upcoming' => Event::where('event_type', 'service')
                ->whereNotNull('service_group_id')
                ->where('start_at', '>=', now())
                ->distinct('service_group_id')
                ->count('service_group_id'),
            'with_two' => Event::where('event_type', 'service')
                ->whereNotNull('service_group_id')
                ->select('service_group_id')
                ->groupBy('service_group_id')
                ->havingRaw('COUNT(*) > 1')
                ->get()
                ->count(),
        ];

        return view('services.index', [
            'serviceGroups' => $groupIds,
            'stats' => $stats,
            'serviceTypes' => Event::serviceTypes(),
        ]);
    }

    public function show(Event $event)
    {
        abort_unless($event->event_type === 'service', 404);

        $sessions = $event->siblings()
            ->with(['preacherMember', 'coordinatorMember', 'elderMember', 'leaderMember', 'churchService'])
            ->get();

        $primary = $sessions->first() ?? $event->load([
            'preacherMember', 'coordinatorMember', 'elderMember', 'leaderMember', 'churchService',
        ]);

        return view('services.show', [
            'service' => $primary,
            'sessions' => $sessions,
        ]);
    }

    public function edit(Event $event)
    {
        abort_unless($event->event_type === 'service', 404);

        $sessions = $event->siblings()
            ->with(['preacherMember.leadershipRoles', 'coordinatorMember', 'elderMember', 'leaderMember'])
            ->get();

        $primary = $sessions->first() ?? $event->load(['preacherMember.leadershipRoles']);

        $preacherType = '';
        if ($primary->preacher_member_id && $primary->preacherMember) {
            $preacherType = $primary->preacherMember->preacherSourceType();
        } elseif ($primary->leader) {
            $preacherType = 'guest';
        }

        return view('services.edit', [
            'service' => $primary,
            'sessions' => $sessions,
            'preacherType' => $preacherType,
            'pastors' => \App\Models\Member::pastors()->orderBy('name')->get(['id', 'name']),
            'leaders' => \App\Models\Member::leaders()->with('leadershipRoles')->orderBy('name')->get(['id', 'name']),
            'elders' => \App\Models\Member::churchElders()->orderBy('name')->get(['id', 'name']),
            'members' => \App\Models\Member::adults()->orderBy('name')->get(['id', 'name']),
            'serviceTypes' => Event::serviceTypes(),
        ]);
    }

    public function update(Request $request, Event $event)
    {
        abort_unless($event->event_type === 'service', 404);

        $validated = $request->validate([
            'theme' => 'nullable|string|max:255',
            'service_date' => 'required|date',
            'preacher_type' => 'nullable|in:pastor,leader,member,guest',
            'preacher_member_id' => 'nullable|exists:members,id',
            'preacher_guest_name' => 'nullable|string|max:255',
            'coordinator_member_id' => 'nullable|exists:members,id',
            'elder_member_id' => 'nullable|exists:members,id',
            'location' => 'nullable|string|max:255',
            'choir' => 'nullable|string|max:255',
            'registered_members_count' => 'nullable|integer|min:0',
            'guests_count' => 'nullable|integer|min:0',
            'scripture_readings' => 'nullable|string|max:5000',
            'announcements' => 'nullable|string|max:5000',
            'sessions' => 'required|array|min:1',
            'sessions.*.id' => 'required|exists:events,id',
            'sessions.*.start_time' => 'required|date_format:H:i',
            'sessions.*.end_time' => 'nullable|date_format:H:i',
        ]);

        $sessions = $event->siblings()->get();
        $sessionIds = $sessions->pluck('id')->all();

        foreach ($validated['sessions'] as $row) {
            if (! in_array((int) $row['id'], $sessionIds, true)) {
                abort(403);
            }
        }

        if (($validated['preacher_type'] ?? '') === 'guest') {
            $preacherId = null;
            $leaderName = trim($validated['preacher_guest_name'] ?? '') ?: null;
        } else {
            $preacherId = $validated['preacher_member_id'] ?? null;
            $leaderName = $preacherId
                ? \App\Models\Member::find($preacherId)?->name
                : null;
        }

        $shared = [
            'theme' => $validated['theme'] ?? null,
            'location' => $validated['location'] ?? null,
            'choir' => $validated['choir'] ?? null,
            'registered_members_count' => $validated['registered_members_count'] ?? null,
            'guests_count' => $validated['guests_count'] ?? null,
            'scripture_readings' => $validated['scripture_readings'] ?? null,
            'announcements' => $validated['announcements'] ?? null,
            'description' => $validated['theme'] ?? null,
            'preacher_member_id' => $preacherId,
            'leader_member_id' => $preacherId,
            'leader' => $leaderName,
            'coordinator_member_id' => $validated['coordinator_member_id'] ?? null,
            'elder_member_id' => $validated['elder_member_id'] ?? null,
        ];

        $date = Carbon::parse($validated['service_date'])->toDateString();

        DB::transaction(function () use ($validated, $shared, $date, $sessions) {
            foreach ($validated['sessions'] as $row) {
                $session = $sessions->firstWhere('id', (int) $row['id']);
                if (! $session) {
                    continue;
                }

                $session->update(array_merge($shared, [
                    'start_at' => Carbon::parse($date . ' ' . $row['start_time']),
                    'end_at' => ! empty($row['end_time'])
                        ? Carbon::parse($date . ' ' . $row['end_time'])
                        : null,
                    'title' => $shared['theme'] ?: ($session->service_type ?: 'Church Service'),
                ]));
            }
        });

        return redirect()
            ->route('services.show', $event)
            ->with('success', __('Service updated successfully.'));
    }

    public function destroy(Event $event)
    {
        abort_unless($event->event_type === 'service', 404);

        if ($event->service_group_id) {
            Event::where('service_group_id', $event->service_group_id)->delete();
        } else {
            $event->delete();
        }

        return redirect()
            ->route('services.index')
            ->with('success', __('Service deleted successfully.'));
    }
}
