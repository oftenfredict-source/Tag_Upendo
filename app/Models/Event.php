<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Event extends Model
{
    protected $fillable = [
        'title',
        'leader',
        'leader_member_id',
        'event_type',
        'service_type',
        'start_at',
        'end_at',
        'all_day',
        'location',
        'description',
        'church_service_id',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'all_day' => 'boolean',
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
            'Sunday Service',
            'Mid-week Service',
            'Prayer Meeting',
            'Special Event',
            'Other',
        ];
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
        if ($this->leader) {
            return $this->title . ' — ' . $this->leader;
        }

        return $this->title;
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

    public static function validationRules(?int $id = null): array
    {
        return [
            'title' => 'required|string|max:255',
            'leader' => 'nullable|string|max:255',
            'leader_member_id' => 'nullable|exists:members,id',
            'event_type' => 'required|in:' . implode(',', array_keys(self::types())),
            'service_type' => 'nullable|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'all_day' => 'nullable|boolean',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
        ];
    }
}
