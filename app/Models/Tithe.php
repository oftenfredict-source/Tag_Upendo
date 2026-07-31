<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tithe extends Model
{
    protected $fillable = [
        'member_id',
        'member_name',
        'amount',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function displayName(): string
    {
        return $this->member?->name ?? $this->member_name ?? '—';
    }
}
