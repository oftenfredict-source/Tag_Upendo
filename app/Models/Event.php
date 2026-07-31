<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Event extends Model
{
    protected $fillable = [
        'service_group_id',
        'title',
        'leader',
        'leader_member_id',
        'event_type',
        'service_type',
        'theme',
        'preacher_member_id',
        'coordinator_member_id',
        'elder_member_id',
        'start_at',
        'end_at',
        'all_day',
        'location',
        'choir',
        'registered_members_count',
        'guests_count',
        'scripture_readings',
        'announcements',
        'description',
        'church_service_id',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'all_day' => 'boolean',
        'registered_members_count' => 'integer',
        'guests_count' => 'integer',
    ];

    public static function types(): array
    {
        return [
            'service' => ['label' => 'Ibada / Service', 'color' => '#009688'],
            'event' => ['label' => 'Event / Tukio', 'color' => '#3f51b5'],
            'prayer' => ['label' => 'Maombi', 'color' => '#9c27b0'],
            'fellowship' => ['label' => 'Fellowship', 'color' => '#ff9800'],
            'other' => ['label' => 'Nyingine', 'color' => '#607d8b'],
        ];
    }

    public static function serviceTypes(): array
    {
        return [
            'First Service (Sunday)' => 'Ibada ya Kwanza — First Service (Sunday)',
            'Second Service (Sunday)' => 'Ibada ya Pili — Second Service (Sunday)',
            'Sunday Service' => 'Ibada ya Jumapili — Sunday Service',
            'Mid-week Service' => 'Ibada ya Wiki — Mid-week Service',
            'Prayer Meeting' => 'Maombi — Prayer Meeting',
            'Special Event' => 'Tukio Maalum — Special Event',
            'Other' => 'Nyingine — Other',
        ];
    }

    public static function serviceTypeLabel(?string $value): string
    {
        if (! $value) {
            return '—';
        }

        return self::serviceTypes()[$value] ?? $value;
    }

    public function color(): string
    {
        return self::types()[$this->event_type]['color'] ?? '#607d8b';
    }

    public function typeLabel(): string
    {
        return self::types()[$this->event_type]['label'] ?? ucfirst($this->event_type);
    }

    public function churchService()
    {
        return $this->belongsTo(ChurchService::class);
    }

    public function leaderMember()
    {
        return $this->belongsTo(Member::class, 'leader_member_id');
    }

    public function preacherMember()
    {
        return $this->belongsTo(Member::class, 'preacher_member_id');
    }

    public function coordinatorMember()
    {
        return $this->belongsTo(Member::class, 'coordinator_member_id');
    }

    public function elderMember()
    {
        return $this->belongsTo(Member::class, 'elder_member_id');
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return $query->whereBetween('start_at', [$start, $end]);
    }

    public static function monthNames(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Machi', 4 => 'Aprili',
            5 => 'Mei', 6 => 'Juni', 7 => 'Julai', 8 => 'Agosti',
            9 => 'Septemba', 10 => 'Oktoba', 11 => 'Novemba', 12 => 'Desemba',
        ];
    }

    public static function monthName(int $month): string
    {
        return self::monthNames()[$month] ?? '';
    }

    public function displayTitle(): string
    {
        $title = $this->title;
        if ($this->theme) {
            $title .= ' — ' . $this->theme;
        } elseif ($this->leader) {
            $title .= ' — ' . $this->leader;
        }

        return $title;
    }

    public function toCalendarEntry(): array
    {
        $entry = [
            'id' => 'ev-' . $this->id,
            'title' => $this->displayTitle(),
            'start' => $this->start_at->toIso8601String(),
            'allDay' => $this->all_day,
            'color' => $this->color(),
            'extendedProps' => [
                'source' => 'event',
                'eventId' => $this->id,
                'title' => $this->title,
                'theme' => $this->theme,
                'leader' => $this->leader,
                'eventType' => $this->event_type,
                'serviceType' => $this->service_type,
                'location' => $this->location,
                'description' => $this->description,
                'allDay' => $this->all_day,
                'startAt' => $this->start_at->toIso8601String(),
                'endAt' => $this->end_at?->toIso8601String(),
                'hasAttendance' => (bool) $this->church_service_id,
            ],
        ];

        if ($this->end_at) {
            $entry['end'] = $this->end_at->toIso8601String();
        }

        return $entry;
    }

    /**
     * One calendar card for a whole service group (e.g. First + Second Sunday).
     */
    public static function toGroupedCalendarEntry($sessions): array
    {
        $sessions = collect($sessions)->sortBy('start_at')->values();
        /** @var Event $primary */
        $primary = $sessions->first();

        $earliest = $sessions->min('start_at');
        $latestEnd = $sessions
            ->map(fn (Event $s) => $s->end_at ?? $s->start_at)
            ->max();

        $sessionCount = $sessions->count();
        $theme = $primary->theme;
        $title = $theme ?: ($sessionCount > 1 ? 'Ibada ya Jumapili' : ($primary->service_type ?: $primary->title));

        if ($sessionCount > 1) {
            $times = $sessions->map(function (Event $s) {
                $label = str_contains((string) $s->service_type, 'Second') ? '2nd' : (str_contains((string) $s->service_type, 'First') ? '1st' : '');
                $t = $s->start_at->format('g:ia');

                return trim($label . ' ' . $t);
            })->implode(' · ');
            $title = $theme ? ($theme . ' (' . $times . ')') : ('Ibada (' . $times . ')');
        } elseif ($theme && $primary->service_type) {
            $title = $theme;
        }

        return [
            'id' => 'grp-' . ($primary->service_group_id ?: $primary->id),
            'title' => $title,
            'start' => $earliest->toIso8601String(),
            'end' => $latestEnd?->toIso8601String(),
            'allDay' => false,
            'color' => $primary->color(),
            'extendedProps' => [
                'source' => 'event',
                'eventId' => $primary->id,
                'serviceGroupId' => $primary->service_group_id,
                'title' => $primary->title,
                'theme' => $theme,
                'leader' => $primary->leader,
                'eventType' => $primary->event_type,
                'serviceType' => $sessionCount > 1 ? 'Sunday Service' : $primary->service_type,
                'sessionCount' => $sessionCount,
                'location' => $primary->location,
                'description' => $primary->description,
                'allDay' => false,
                'startAt' => $earliest->toIso8601String(),
                'endAt' => $latestEnd?->toIso8601String(),
                'hasAttendance' => $sessions->contains(fn (Event $s) => (bool) $s->church_service_id),
            ],
        ];
    }

    public function siblings()
    {
        if (! $this->service_group_id) {
            return Event::where('id', $this->id);
        }

        return Event::where('service_group_id', $this->service_group_id)->orderBy('start_at');
    }

    public function displayDayTitle(): string
    {
        if ($this->theme) {
            return $this->theme;
        }

        return 'Ibada — ' . $this->start_at->format('d/m/Y');
    }

    /**
     * Status from service time: scheduled | ongoing | completed
     */
    public function computedStatus(): string
    {
        $now = now();

        if ($now->lt($this->start_at)) {
            return 'scheduled';
        }

        $end = $this->end_at ?? $this->start_at->copy()->addHours(2);

        if ($now->lte($end)) {
            return 'ongoing';
        }

        return 'completed';
    }

    /** Attendance only during or after service start time. */
    public function canRecordAttendance(): bool
    {
        return $this->computedStatus() !== 'scheduled';
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'scheduled' => __('Scheduled'),
            'ongoing' => __('Ongoing'),
            'completed' => __('Completed'),
            default => ucfirst($status),
        };
    }

    public static function statusBadge(string $status): string
    {
        return match ($status) {
            'scheduled' => 'info',
            'ongoing' => 'warning',
            'completed' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Group status from earliest start / latest end across sessions.
     */
    public static function groupComputedStatus($sessions): string
    {
        $sessions = collect($sessions);
        if ($sessions->isEmpty()) {
            return 'scheduled';
        }

        $now = now();
        $earliest = $sessions->min('start_at');
        $latestEnd = $sessions->map(fn ($s) => $s->end_at ?? $s->start_at->copy()->addHours(2))->max();

        if ($now->lt($earliest)) {
            return 'scheduled';
        }

        if ($now->lte($latestEnd)) {
            return 'ongoing';
        }

        return 'completed';
    }

    public static function validationRules(?int $id = null): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'leader' => 'nullable|string|max:255',
            'leader_member_id' => 'nullable|exists:members,id',
            'event_type' => 'nullable|in:' . implode(',', array_keys(self::types())),
            'service_type' => 'nullable|string|max:255',
            'theme' => 'nullable|string|max:255',
            'preacher_member_id' => 'nullable|exists:members,id',
            'preacher_type' => 'nullable|in:pastor,leader,member,guest',
            'preacher_guest_name' => 'nullable|string|max:255',
            'coordinator_member_id' => 'nullable|exists:members,id',
            'elder_member_id' => 'nullable|exists:members,id',
            'service_date' => 'required_without:start_at|nullable|date',
            'has_two_services' => 'nullable|boolean',
            'first_start_time' => 'nullable|date_format:H:i',
            'first_end_time' => 'nullable|date_format:H:i',
            'second_start_time' => 'nullable|date_format:H:i',
            'second_end_time' => 'nullable|date_format:H:i',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'start_at' => 'required_without:service_date|nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'all_day' => 'nullable|boolean',
            'location' => 'nullable|string|max:255',
            'choir' => 'nullable|string|max:255',
            'registered_members_count' => 'nullable|integer|min:0',
            'guests_count' => 'nullable|integer|min:0',
            'scripture_readings' => 'nullable|string|max:5000',
            'announcements' => 'nullable|string|max:5000',
            'description' => 'nullable|string|max:2000',
        ];
    }
}