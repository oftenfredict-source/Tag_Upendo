<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'member_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'member_id' => 'integer',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function isAdmin(): bool
    {
        return ($this->role ?? 'admin') === 'admin';
    }

    public function isPastor(): bool
    {
        if ($this->role === 'pastor') {
            return true;
        }

        if ($this->member_id) {
            $member = $this->relationLoaded('member')
                ? $this->member
                : $this->member()->with('leadershipRoles')->first();

            return $member?->isPastor() ?? false;
        }

        return false;
    }

    public function isSecretary(): bool
    {
        if ($this->role === 'secretary') {
            return true;
        }

        if ($this->member_id) {
            $member = $this->relationLoaded('member')
                ? $this->member
                : $this->member()->with('leadershipRoles')->first();

            return $member?->isSecretary() ?? false;
        }

        return false;
    }

    public function isMember(): bool
    {
        if ($this->isAdmin() || $this->isPastor() || $this->isSecretary()) {
            return false;
        }

        return $this->role === 'member';
    }

    public function isStaff(): bool
    {
        return $this->isAdmin() || $this->isPastor() || $this->isSecretary();
    }

    public function isFullStaff(): bool
    {
        return $this->isAdmin() || $this->isPastor() || $this->isSecretary();
    }

    public function canManageSettings(): bool
    {
        return $this->isAdmin() || $this->isPastor();
    }

    public function canManageServiceRequests(): bool
    {
        return $this->isAdmin() || $this->isPastor();
    }

    public function canManageMemberRegistrations(): bool
    {
        return $this->isAdmin() || $this->isPastor();
    }

    public function canSubmitOwnServiceRequests(): bool
    {
        return $this->isMember() || $this->isSecretary();
    }

    public function hasMemberProfile(): bool
    {
        return (bool) $this->member_id;
    }

    public function memberPortalRouteName(): string
    {
        return $this->isMember() ? 'dashboard' : 'my.portal';
    }

    public function roleLabel(): string
    {
        if ($this->isAdmin()) {
            return __('Administrator');
        }

        if ($this->isPastor()) {
            return __('Pastor');
        }

        if ($this->isSecretary()) {
            return __('Secretary');
        }

        return __('Member');
    }
}
