<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\NotificationService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        View::composer(['layouts.app', 'auth.login', 'layouts.public-form'], function ($view) {
            $churchDefaults = [
                'appChurchName' => 'TAG Upendo',
                'appChurchTagline' => '',
                'appChurchLogo' => null,
            ];

            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                    $logoPath = Setting::get('church_logo');
                    $view->with([
                        'appChurchName' => Setting::get('church_name', 'TAG Upendo'),
                        'appChurchTagline' => Setting::get('church_tagline', ''),
                        'appChurchLogo' => $logoPath ? asset('storage/' . ltrim($logoPath, '/')) : null,
                    ]);
                } else {
                    $view->with($churchDefaults);
                }
            } catch (\Throwable) {
                $view->with($churchDefaults);
            }

            if ($view->name() === 'layouts.app') {
                $user = auth()->user();
                $view->with('headerNotifications', $user
                    ? app(NotificationService::class)->forUser($user)
                    : ['count' => 0, 'items' => []]);
            }
        });
    }
}
