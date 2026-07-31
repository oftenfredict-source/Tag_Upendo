<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Announcement extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'audience',
        'priority',
        'is_published',
        'starts_at',
        'expires_at',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public static function audiences(): array
    {
        return [
            'all' => __('Everyone'),
            'members' => __('Members only'),
            'staff' => __('Staff only'),
        ];
    }

    public static function priorities(): array
    {
        return [
            'normal' => __('Normal'),
            'important' => __('Important'),
        ];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function audienceLabel(): string
    {
        return self::audiences()[$this->audience] ?? $this->audience;
    }

    public function priorityLabel(): string
    {
        return self::priorities()[$this->priority] ?? $this->priority;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            });
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $audiences = ['all'];

        if ($user->isStaff()) {
            $audiences[] = 'staff';
        }

        if ($user->isMember() || $user->hasMemberProfile()) {
            $audiences[] = 'members';
        }

        return $query->published()->active()->whereIn('audience', $audiences);
    }

    public static function feedFor(User $user, int $limit = 5)
    {
        return static::with('author')
            ->visibleTo($user)
            ->latest('published_at')
            ->latest('id')
            ->take($limit)
            ->get();
    }
}
