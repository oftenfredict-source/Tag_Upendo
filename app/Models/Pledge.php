<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pledge extends Model
{
    protected $fillable = [
        'member_id',
        'member_name',
        'pledge_for',
        'amount',
        'amount_paid',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function payments()
    {
        return $this->hasMany(PledgePayment::class)->latest('payment_date')->latest('id');
    }

    public function displayName(): string
    {
        return $this->member?->name ?? $this->member_name ?? '—';
    }

    public function remainingAmount(): float
    {
        return max(0, (float) $this->amount - (float) $this->amount_paid);
    }

    public function paidPercent(): float
    {
        if ((float) $this->amount <= 0) {
            return 0;
        }

        return min(100, round(((float) $this->amount_paid / (float) $this->amount) * 100, 1));
    }

    public function remainingPercent(): float
    {
        return max(0, round(100 - $this->paidPercent(), 1));
    }

    public function syncStatus(): void
    {
        $paid = (float) $this->amount_paid;
        $total = (float) $this->amount;

        if ($paid <= 0) {
            $this->status = 'pending';
        } elseif ($paid >= $total) {
            $this->status = 'completed';
            $this->amount_paid = $total;
        } else {
            $this->status = 'partial';
        }
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'completed' => __('Completed'),
            'partial' => __('Partial'),
            default => __('Pending'),
        };
    }

    public static function statusBadge(string $status): string
    {
        return match ($status) {
            'completed' => 'success',
            'partial' => 'warning',
            default => 'secondary',
        };
    }
}
