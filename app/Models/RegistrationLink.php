<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RegistrationLink extends Model
{
    protected $fillable = [
        'token',
        'created_by',
        'label',
        'is_active',
        'expires_at',
        'uses_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'uses_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $link) {
            if (! $link->token) {
                $link->token = Str::random(48);
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function requests()
    {
        return $this->hasMany(MemberRegistrationRequest::class);
    }

    public function isUsable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function publicUrl(): string
    {
        return route('register.show', $this->token);
    }

    public static function findUsable(string $token): ?self
    {
        $link = static::where('token', $token)->first();

        return $link && $link->isUsable() ? $link : null;
    }
}
