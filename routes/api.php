<?php

use Illuminate\Support\Facades\Route;
use WeiJuKeJi\ScheduleMonitorApi\Http\Controllers\ScheduleMonitorController;

Route::middleware(config('schedule-monitor-api.middleware', ['auth:sanctum']))
    ->prefix(config('schedule-monitor-api.route_prefix', 'api/v1/system/schedule-tasks'))
    ->name(config('schedule-monitor-api.route_name', 'system.schedule-tasks') . '.')
    ->group(function () {
        Route::get('/', [ScheduleMonitorController::class, 'index'])->name('index');
        Route::get('{id}/logs', [ScheduleMonitorController::class, 'logs'])->name('logs');
    });
