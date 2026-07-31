<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'member_code',
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
        'guardian_name',
        'guardian_phone',
        'archived_at',
        'archive_reason',
        'archived_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_joined' => 'date',
        'is_baptized' => 'boolean',
        'baptism_date' => 'date',
        'archived_at' => 'datetime',
    ];

    public function archivedBy()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function leadershipRoles()
    {
        return $this->belongsToMany(LeadershipRole::class, 'member_leadership_role')
            ->withPivot(['assigned_at', 'notes'])
            ->withTimestamps()
            ->orderBy('sort_order');
    }

    public function tithes()
    {
        return $this->hasMany(Tithe::class);
    }

    public function pledges()
    {
        return $this->hasMany(Pledge::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
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
        return $this->parent_id !== null || $this->hasExternalGuardian();
    }

    public function getAgeAttribute(): ?int
    {
        if (! $this->date_of_birth) {
            return null;
        }

        return (int) $this->date_of_birth->age;
    }

    public function getBirthYearAttribute(): ?int
    {
        if (! $this->date_of_birth) {
            return null;
        }

        return (int) $this->date_of_birth->format('Y');
    }

    public function hasExternalGuardian(): bool
    {
        return $this->parent_id === null && filled($this->guardian_name);
    }

    public function guardianDisplayName(): ?string
    {
        if ($this->parent) {
            return $this->parent->name;
        }

        return $this->guardian_name;
    }

    public function guardianDisplayPhone(): ?string
    {
        if ($this->parent) {
            return $this->parent->phone_number;
        }

        return $this->guardian_phone;
    }

    public function hasSpouse(): bool
    {
        return $this->spouse_id !== null;
    }

    public const MAX_CHILD_AGE = 18;

    public function scopeAdults($query)
    {
        return $query->whereNull('parent_id');
    }

    /** Watoto wenye umri wa miaka 0–18 (kulingana na tarehe ya kuzaliwa). */
    public function scopeChildrenByAge($query, int $minAge = 0, int $maxAge = self::MAX_CHILD_AGE)
    {
        return $query->whereNotNull('date_of_birth')
            ->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) >= ?', [$minAge])
            ->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) <= ?', [$maxAge]);
    }

    public function scopePastors($query)
    {
        return $query->adults()->whereHas('leadershipRoles', function ($q) {
            $q->where('name', 'like', '%Pastor%');
        });
    }

    public function scopeLeaders($query)
    {
        return $query->adults()->whereHas('leadershipRoles');
    }

    public function scopeChurchElders($query)
    {
        return $query->adults()->whereHas('leadershipRoles', function ($q) {
            $q->where('name', 'Elder')
                ->orWhere('name_sw', 'Mzee wa Kanisa');
        });
    }

    public function scopeSecretaries($query)
    {
        return $query->adults()->whereHas('leadershipRoles', function ($q) {
            $q->where('name', 'like', '%Secretary%')
                ->orWhere('name_sw', 'like', '%Katibu%');
        });
    }

    public function isPastor(): bool
    {
        return $this->leadershipRoles->contains(function ($role) {
            return str_contains($role->name, 'Pastor')
                || str_contains($role->name_sw ?? '', 'Mchungaji');
        });
    }

    public function isSecretary(): bool
    {
        return $this->leadershipRoles->contains(function ($role) {
            return str_contains($role->name, 'Secretary')
                || str_contains($role->name_sw ?? '', 'Katibu');
        });
    }

    public function isLeader(): bool
    {
        return $this->leadershipRoles->isNotEmpty();
    }

    public function preacherSourceType(): string
    {
        if ($this->isPastor()) {
            return 'pastor';
        }

        if ($this->isLeader()) {
            return 'leader';
        }

        return 'member';
    }
}
