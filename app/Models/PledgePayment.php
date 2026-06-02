<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PledgePayment extends Model
{
    protected $fillable = [
        'pledge_id',
        'amount',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function pledge()
    {
        return $this->belongsTo(Pledge::class);
    }
}
