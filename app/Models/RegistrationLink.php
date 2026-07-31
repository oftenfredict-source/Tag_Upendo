<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RegistrationLink extends Model
{
    protected $fillable = [
        'token',
        'short_code',
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

            if (! $link->short_code) {
                $link->short_code = static::generateUniqueShortCode();
            }
        });
    }

    public static function generateUniqueShortCode(): string
    {
        do {
            $code = Str::lower(Str::random(8));
        } while (static::where('short_code', $code)->exists());

        return $code;
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

    public function publicCode(): string
    {
        return $this->short_code ?: $this->token;
    }

    public function publicUrl(): string
    {
        return route('register.show', $this->publicCode());
    }

    public static function findUsable(string $code): ?self
    {
        $link = static::where('short_code', $code)->first()
            ?? static::where('token', $code)->first();

        return $link && $link->isUsable() ? $link : null;
    }

    public static function findByPublicCode(string $code): ?self
    {
        return static::where('short_code', $code)->first()
            ?? static::where('token', $code)->first();
    }

    public static function activeLink(): ?self
    {
        return static::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->first();
    }
}
