<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'name',
        'phone_number',
        'email',
        'gender',
        'date_of_birth',
        'birth_mkoa',
        'birth_wilaya',
        'residence_mkoa',
        'residence_wilaya',
        'address',
        'marital_status',
        'date_joined',
        'is_baptized',
        'baptism_date',
        'occupation',
        'member_type',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
        'department_id',
        'spouse_id',
        'parent_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_joined' => 'date',
        'is_baptized' => 'boolean',
        'baptism_date' => 'date',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function leadershipRoles()
    {
        return $this->belongsToMany(LeadershipRole::class, 'member_leadership_role')
            ->withPivot(['assigned_at', 'notes'])
            ->withTimestamps()
            ->orderBy('sort_order');
    }

    public function leadershipRolesLabel(): string
    {
        return $this->leadershipRoles->map(fn ($r) => $r->shortLabel())->implode(', ');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function spouse()
    {
        return $this->belongsTo(Member::class, 'spouse_id');
    }

    public function parent()
    {
        return $this->belongsTo(Member::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Member::class, 'parent_id');
    }

    /** IDs ya wazazi wote (mwanachama + mwenzi wake). */
    public function familyParentIds(): array
    {
        return array_values(array_filter([$this->id, $this->spouse_id]));
    }

    /** Watoto wa familia — wanaonekana kwa mzazi na mwenzi. */
    public function familyChildren()
    {
        return Member::whereIn('parent_id', $this->familyParentIds())->orderBy('name');
    }

    public function isChild(): bool
    {
        return $this->parent_id !== null;
    }

    public function hasSpouse(): bool
    {
        return $this->spouse_id !== null;
    }
}
