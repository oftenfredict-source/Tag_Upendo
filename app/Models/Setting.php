<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'label'];

    protected static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (empty(static::$cache)) {
            static::loadCache();
        }

        return static::$cache[$key] ?? $default;
    }

    public static function set(string $key, mixed $value, ?string $group = null, ?string $label = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            array_filter([
                'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                'group' => $group,
                'label' => $label,
            ], fn ($v) => $v !== null)
        );

        static::clearCache();
    }

    public static function group(string $group): array
    {
        if (empty(static::$cache)) {
            static::loadCache();
        }

        return static::query()
            ->where('group', $group)
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn (self $s) => [$s->key => $s->value])
            ->all();
    }

    public static function allGrouped(): array
    {
        return static::orderBy('group')
            ->orderBy('id')
            ->get()
            ->groupBy('group')
            ->all();
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = static::get($key, $default ? '1' : '0');

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    protected static function loadCache(): void
    {
        static::$cache = Cache::remember('app_settings', 3600, function () {
            return static::pluck('value', 'key')->all();
        });
    }

    public static function clearCache(): void
    {
        static::$cache = [];
        Cache::forget('app_settings');
    }
}
