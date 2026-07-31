<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadershipRole extends Model
{
    protected $fillable = [
        'name',
        'name_sw',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function members()
    {
        return $this->belongsToMany(Member::class, 'member_leadership_role')
            ->withPivot(['assigned_at', 'notes'])
            ->withTimestamps()
            ->orderBy('name');
    }

    public function displayName(): string
    {
        return $this->name_sw ? $this->name_sw . ' (' . $this->name . ')' : $this->name;
    }

    public function shortLabel(): string
    {
        return $this->name_sw ?: $this->name;
    }

    public function label(): string
    {
        if (app()->getLocale() === 'sw' && $this->name_sw) {
            return $this->name_sw;
        }

        return $this->name;
    }

    public static function activeOrdered()
    {
        return static::where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }
}
