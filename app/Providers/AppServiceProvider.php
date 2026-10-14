<?php

namespace App\Providers;

use App\Services\SettingService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.public', 'layouts.admin', 'layouts.guest'], function ($view) {
            $settings = app(SettingService::class);
            $view->with('eventName', $settings->get('event_name'))
                ->with('organizationName', $settings->get('organization_name'));
        });
    }
}
