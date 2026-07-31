<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceGuest extends Model
{
    public const TYPE_VISITED = 'visited';

    public const TYPE_PROMISED = 'promised';

    public const STATUS_RECORDED = 'recorded';

    public const STATUS_PENDING = 'pending';

    public const STATUS_ATTENDED = 'attended';

    public const STATUS_MISSED = 'missed';

    protected $fillable = [
        'guest_type',
        'name',
        'phone',
        'email',
        'coming_from',
        'event_id',
        'service_date',
        'notes',
        'status',
        'reminder_sent_at',
        'thank_you_sent_at',
        'recorded_by',
        'promised_guest_id',
    ];

    protected $casts = [
        'service_date' => 'date',
        'reminder_sent_at' => 'datetime',
        'thank_you_sent_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function promisedGuest()
    {
        return $this->belongsTo(self::class, 'promised_guest_id');
    }

    public function isPromised(): bool
    {
        return $this->guest_type === self::TYPE_PROMISED;
    }

    public function isVisited(): bool
    {
        return $this->guest_type === self::TYPE_VISITED;
    }

    public function canSendReminder(): bool
    {
        return $this->isPromised()
            && filled($this->phone)
            && in_array($this->status, [self::STATUS_PENDING, self::STATUS_ATTENDED], true);
    }

    public function canSendThankYou(): bool
    {
        return filled($this->phone)
            && ($this->isVisited() || $this->status === self::STATUS_ATTENDED);
    }

    public function serviceDateLabel(): string
    {
        if ($this->service_date) {
            return $this->service_date->format('d/m/Y');
        }

        return $this->event?->start_at?->format('d/m/Y') ?? '—';
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_VISITED => __('Visited guest'),
            self::TYPE_PROMISED => __('Promised guest'),
            default => $type,
        };
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            self::STATUS_RECORDED => __('Recorded'),
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_ATTENDED => __('Attended'),
            self::STATUS_MISSED => __('Did not attend'),
            default => $status ?? '—',
        };
    }

    public static function statusBadge(?string $status): string
    {
        return match ($status) {
            self::STATUS_RECORDED => 'info',
            self::STATUS_PENDING => 'warning',
            self::STATUS_ATTENDED => 'success',
            self::STATUS_MISSED => 'secondary',
            default => 'light',
        };
    }

    public function defaultReminderMessage(): string
    {
        $date = $this->serviceDateLabel();

        return trans('Guest reminder SMS', [
            'name' => $this->name,
            'date' => $date,
        ], 'sw');
    }

    public function defaultThankYouMessage(): string
    {
        return trans('Guest thank you SMS', [
            'name' => $this->name,
        ], 'sw');
    }
}
