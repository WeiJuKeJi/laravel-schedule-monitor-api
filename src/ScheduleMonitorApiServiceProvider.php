<?php

namespace WeiJuKeJi\ScheduleMonitorApi;

use Illuminate\Support\ServiceProvider;

class ScheduleMonitorApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/schedule-monitor-api.php' => config_path('schedule-monitor-api.php'),
            ], 'schedule-monitor-api-config');
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/schedule-monitor-api.php',
            'schedule-monitor-api'
        );
    }
}
