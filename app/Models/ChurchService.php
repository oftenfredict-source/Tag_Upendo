<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChurchService extends Model
{
    protected $fillable = [
        'service_date',
        'service_type',
        'title',
        'leader',
        'notes',
    ];

    protected $casts = [
        'service_date' => 'date',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'church_service_id');
    }

    public function presentMembers()
    {
        return $this->belongsToMany(Member::class, 'attendances')
            ->withTimestamps()
            ->withPivot('status');
    }

    public function displayName(): string
    {
        $name = $this->title ?: $this->service_type;

        if ($this->leader) {
            return $name . ' — ' . $this->leader;
        }

        return $name;
    }
}
