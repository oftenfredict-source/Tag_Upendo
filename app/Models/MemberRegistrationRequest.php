<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberRegistrationRequest extends Model
{
    protected $fillable = [
        'registration_link_id',
        'status',
        'payload',
        'applicant_name',
        'applicant_phone',
        'applicant_email',
        'member_id',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'payload' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function link()
    {
        return $this->belongsTo(RegistrationLink::class, 'registration_link_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'approved' => __('Approved'),
            'rejected' => __('Rejected'),
            default => __('Pending'),
        };
    }

    public static function statusBadge(string $status): string
    {
        return match ($status) {
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'warning',
        };
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
