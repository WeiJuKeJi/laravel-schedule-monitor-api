<?php

namespace WeiJuKeJi\ScheduleMonitorApi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTask;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTaskLogItem;
use WeiJuKeJi\ScheduleMonitorApi\Http\Resources\ScheduleTaskLogResource;
use WeiJuKeJi\ScheduleMonitorApi\Http\Resources\ScheduleTaskResource;
use WeiJuKeJi\ScheduleMonitorApi\Support\TaskStatusResolver;

class ScheduleMonitorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', config('schedule-monitor-api.per_page', 20));
        $status = $request->get('status');

        $query = MonitoredScheduledTask::query()->orderBy('name');

        $tasks = $query->get();

        // 状态筛选在内存中完成（数据量小，无需 DB 计算）
        if ($status && in_array($status, [
            TaskStatusResolver::STATUS_HEALTHY,
            TaskStatusResolver::STATUS_WARNING,
            TaskStatusResolver::STATUS_FAILED,
            TaskStatusResolver::STATUS_NEVER_RAN,
        ])) {
            $tasks = $tasks->filter(
                fn ($task) => TaskStatusResolver::resolve($task) === $status
            )->values();
        }

        // 手动分页
        $page = (int) $request->get('page', 1);
        $total = $tasks->count();
        $list = $tasks->forPage($page, $perPage)->values();

        return response()->json([
            'code' => 200,
            'msg'  => 'success',
            'data' => [
                'list'  => ScheduleTaskResource::collection($list),
                'total' => $total,
            ],
        ]);
    }

    public function logs(Request $request, int $id): JsonResponse
    {
        $task = MonitoredScheduledTask::findOrFail($id);

        $perPage = (int) $request->get('per_page', config('schedule-monitor-api.log_per_page', 50));

        $logs = MonitoredScheduledTaskLogItem::query()
            ->where('monitored_scheduled_task_id', $task->id)
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'code' => 200,
            'msg'  => 'success',
            'data' => [
                'task'  => new ScheduleTaskResource($task),
                'list'  => ScheduleTaskLogResource::collection($logs->items()),
                'total' => $logs->total(),
            ],
        ]);
    }
}
