<?php

namespace WeiJuKeJi\ScheduleMonitorApi\Support;

use Carbon\Carbon;
use Cron\CronExpression;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTask;

class TaskStatusResolver
{
    public const STATUS_HEALTHY = 'healthy';
    public const STATUS_WARNING = 'warning';
    public const STATUS_FAILED = 'failed';
    public const STATUS_NEVER_RAN = 'never_ran';

    public static function resolve(MonitoredScheduledTask $task): string
    {
        if (is_null($task->last_started_at)) {
            return self::STATUS_NEVER_RAN;
        }

        if (self::hasFailed($task)) {
            return self::STATUS_FAILED;
        }

        if (self::isOverdue($task)) {
            return self::STATUS_WARNING;
        }

        return self::STATUS_HEALTHY;
    }

    private static function hasFailed(MonitoredScheduledTask $task): bool
    {
        if (is_null($task->last_failed_at)) {
            return false;
        }

        if (is_null($task->last_finished_at)) {
            return true;
        }

        return $task->last_failed_at->greaterThan($task->last_finished_at);
    }

    private static function isOverdue(MonitoredScheduledTask $task): bool
    {
        $lastFinishedAt = $task->last_finished_at
            ?? $task->created_at->subSecond();

        $timezone = $task->timezone ?? 'UTC';
        $graceMinutes = $task->grace_time_in_minutes ?? 0;

        $nextExpectedRun = Carbon::instance(
            (new CronExpression($task->cron_expression))
                ->getNextRunDate($lastFinishedAt->toDateTimeString(), 0, false, $timezone)
        );

        return $nextExpectedRun->addMinutes($graceMinutes)->isPast();
    }
}
