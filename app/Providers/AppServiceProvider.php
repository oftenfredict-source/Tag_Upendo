<?php

namespace App\Providers;

use App\Models\Setting;
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

        View::composer('layouts.app', function ($view) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                    $view->with([
                        'appChurchName' => Setting::get('church_name', 'TAG Upendo'),
                        'appChurchTagline' => Setting::get('church_tagline', ''),
                    ]);
                    return;
                }
            } catch (\Throwable) {
                //
            }

            $view->with([
                'appChurchName' => 'TAG Upendo',
                'appChurchTagline' => '',
            ]);
        });
    }
}
