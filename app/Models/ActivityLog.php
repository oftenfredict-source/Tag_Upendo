<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deviceLabel(): string
    {
        $parts = array_filter([
            $this->device_type,
            $this->browser,
            $this->platform,
        ]);

        return $parts ? implode(' · ', $parts) : '—';
    }
}
