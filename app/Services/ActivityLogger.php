<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogger
{
    public static function log(string $action, string $description, ?User $user = null, ?Request $request = null): void
    {
        $request ??= request();
        $user ??= auth()->user();
        $agent = self::parseUserAgent($request?->userAgent());

        ActivityLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'action' => $action,
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'device_type' => $agent['device'],
            'browser' => $agent['browser'],
            'platform' => $agent['platform'],
        ]);
    }

    /** @return array{device: string, browser: string, platform: string} */
    public static function parseUserAgent(?string $userAgent): array
    {
        $ua = $userAgent ?? '';

        $device = __('Desktop');
        if (preg_match('/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/i', $ua)) {
            $device = preg_match('/ipad|tablet/i', $ua) ? __('Tablet') : __('Mobile');
        }

        $browser = __('Unknown browser');
        if (preg_match('/Edg\//i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/OPR\//i', $ua) || preg_match('/Opera/i', $ua)) {
            $browser = 'Opera';
        } elseif (preg_match('/Chrome\//i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox\//i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari\//i', $ua)) {
            $browser = 'Safari';
        }

        $platform = __('Unknown OS');
        if (preg_match('/Windows NT/i', $ua)) {
            $platform = 'Windows';
        } elseif (preg_match('/Android/i', $ua)) {
            $platform = 'Android';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $ua)) {
            $platform = 'iOS';
        } elseif (preg_match('/Mac OS X/i', $ua)) {
            $platform = 'macOS';
        } elseif (preg_match('/Linux/i', $ua)) {
            $platform = 'Linux';
        }

        return [
            'device' => $device,
            'browser' => $browser,
            'platform' => $platform,
        ];
    }
}
