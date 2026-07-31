<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $fillable = [
        'member_id',
        'request_type',
        'subject',
        'message',
        'preferred_date',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'preferred_date' => 'date',
    ];

    public static function types(): array
    {
        return [
            'prayer' => __('Prayer request'),
            'pastoral_visit' => __('Pastoral visit'),
            'counseling' => __('Counseling'),
            'baptism' => __('Baptism inquiry'),
            'other' => __('Other'),
        ];
    }

    public function typeLabel(): string
    {
        return self::types()[$this->request_type] ?? $this->request_type;
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'in_progress' => __('In progress'),
            'completed' => __('Completed'),
            'cancelled' => __('Cancelled'),
            default => __('Pending'),
        };
    }

    public static function statusBadge(string $status): string
    {
        return match ($status) {
            'in_progress' => 'info',
            'completed' => 'success',
            'cancelled' => 'secondary',
            default => 'warning',
        };
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
