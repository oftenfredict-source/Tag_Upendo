<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeadershipController extends Controller
{
    public function index(Request $request)
    {
        $month = max(1, min(12, (int) $request->input('month', now()->month)));
        $year = max(2020, min(2100, (int) $request->input('year', now()->year)));

        $current = Carbon::create($year, $month, 1);
        $prev = $current->copy()->subMonth();
        $next = $current->copy()->addMonth();

        $events = Event::with('leaderMember')
            ->forMonth($year, $month)
            ->orderBy('start_at')
            ->get();

        $members = Member::orderBy('name')->get(['id', 'name']);

        return view('leadership.index', [
            'events' => $events,
            'members' => $members,
            'month' => $month,
            'year' => $year,
            'monthLabel' => Event::monthName($month) . ' ' . $year,
            'prev' => $prev,
            'next' => $next,
            'eventTypes' => Event::types(),
            'serviceTypes' => Event::serviceTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_type' => 'required|in:' . implode(',', array_keys(Event::types())),
            'service_type' => 'nullable|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'leader_member_id' => 'nullable|exists:members,id',
            'leader' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020|max:2100',
        ]);

        $validated['all_day'] = false;
        $validated = $this->applyLeader($validated);

        Event::create($validated);

        $month = (int) ($validated['month'] ?? Carbon::parse($validated['start_at'])->month);
        $year = (int) ($validated['year'] ?? Carbon::parse($validated['start_at'])->year);

        return redirect()
            ->route('leadership.index', ['month' => $month, 'year' => $year])
            ->with('success', 'Ibada imeundwa.');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'events' => 'required|array',
            'events.*.leader_member_id' => 'nullable|exists:members,id',
            'events.*.leader' => 'nullable|string|max:255',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        foreach ($validated['events'] as $eventId => $row) {
            $event = Event::find($eventId);
            if (!$event) {
                continue;
            }

            $data = $this->applyLeader([
                'leader_member_id' => $row['leader_member_id'] ?? null,
                'leader' => $row['leader'] ?? null,
            ]);

            $event->update($data);
        }

        return redirect()
            ->route('leadership.index', [
                'month' => $validated['month'],
                'year' => $validated['year'],
            ])
            ->with('success', 'Viongozi wamehifadhiwa.');
    }

    public function destroy(Event $event, Request $request)
    {
        $month = (int) $request->input('month', $event->start_at->month);
        $year = (int) $request->input('year', $event->start_at->year);

        $event->delete();

        return redirect()
            ->route('leadership.index', compact('month', 'year'))
            ->with('success', 'Ibada imefutwa.');
    }

    protected function applyLeader(array $data): array
    {
        if (!empty($data['leader_member_id'])) {
            $member = Member::find($data['leader_member_id']);
            $data['leader'] = $member?->name;
        } else {
            $data['leader_member_id'] = null;
            $data['leader'] = trim($data['leader'] ?? '') ?: null;
        }

        return $data;
    }
}
