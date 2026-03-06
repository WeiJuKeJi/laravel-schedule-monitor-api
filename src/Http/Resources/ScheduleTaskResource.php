<?php

namespace WeiJuKeJi\ScheduleMonitorApi\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use WeiJuKeJi\ScheduleMonitorApi\Support\TaskStatusResolver;

class ScheduleTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'type'                 => $this->type,
            'cron_expression'      => $this->cron_expression,
            'timezone'             => $this->timezone,
            'grace_time_in_minutes' => $this->grace_time_in_minutes,
            'status'               => TaskStatusResolver::resolve($this->resource),
            'last_started_at'      => $this->last_started_at?->toDateTimeString(),
            'last_finished_at'     => $this->last_finished_at?->toDateTimeString(),
            'last_failed_at'       => $this->last_failed_at?->toDateTimeString(),
            'last_skipped_at'      => $this->last_skipped_at?->toDateTimeString(),
            'created_at'           => $this->created_at?->toDateTimeString(),
            'updated_at'           => $this->updated_at?->toDateTimeString(),
        ];
    }
}
