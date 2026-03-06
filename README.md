# Laravel Schedule Monitor API

为 [spatie/laravel-schedule-monitor](https://github.com/spatie/laravel-schedule-monitor) 提供 REST API 接口，将定时任务的监控状态和执行日志以 JSON 格式暴露出来，方便前端或其他服务消费。

## 依赖

- PHP >= 8.3
- Laravel >= 12.0
- [spatie/laravel-schedule-monitor](https://github.com/spatie/laravel-schedule-monitor) ^4.0

## 安装

```bash
composer require weijukeji/laravel-schedule-monitor-api
```

发布配置文件（可选）：

```bash
php artisan vendor:publish --tag=schedule-monitor-api-config
```

## 配置

配置文件位于 `config/schedule-monitor-api.php`：

```php
return [
    // API 路由前缀
    'route_prefix' => 'api/v1/system/schedule-tasks',

    // 路由名称前缀
    'route_name' => 'system.schedule-tasks',

    // 路由中间件
    'middleware' => ['auth:sanctum'],

    // 任务列表每页数量
    'per_page' => 20,

    // 日志列表每页数量
    'log_per_page' => 50,
];
```

## API 接口

### 获取任务列表

```
GET /api/v1/system/schedule-tasks
```

**查询参数：**

| 参数 | 类型 | 说明 |
|------|------|------|
| `page` | int | 页码，默认 1 |
| `per_page` | int | 每页数量，默认 20 |
| `status` | string | 按状态筛选，见下方状态说明 |

**响应示例：**

```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "list": [
            {
                "id": 1,
                "name": "同步订单数据",
                "type": "command",
                "cron_expression": "0 * * * *",
                "timezone": "Asia/Shanghai",
                "grace_time_in_minutes": 5,
                "status": "healthy",
                "last_started_at": "2026-03-06 09:00:00",
                "last_finished_at": "2026-03-06 09:00:01",
                "last_failed_at": null,
                "last_skipped_at": null,
                "created_at": "2026-01-01 00:00:00",
                "updated_at": "2026-03-06 09:00:01"
            }
        ],
        "total": 1
    }
}
```

---

### 获取任务日志

```
GET /api/v1/system/schedule-tasks/{id}/logs
```

**路径参数：**

| 参数 | 类型 | 说明 |
|------|------|------|
| `id` | int | 任务 ID |

**查询参数：**

| 参数 | 类型 | 说明 |
|------|------|------|
| `page` | int | 页码，默认 1 |
| `per_page` | int | 每页数量，默认 50 |

**响应示例：**

```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "task": { ... },
        "list": [
            {
                "id": 100,
                "type": "finished",
                "meta": [],
                "created_at": "2026-03-06 09:00:01"
            }
        ],
        "total": 100
    }
}
```

---

## 任务状态说明

状态由 `TaskStatusResolver` 按优先级依次判断：

| 状态 | 值 | 说明 |
|------|----|------|
| 从未运行 | `never_ran` | 任务自创建以来从未执行过 |
| 失败 | `failed` | 最近一次执行失败，且之后未成功完成 |
| 警告 | `warning` | 任务已超过预期执行时间 + 宽限期仍未完成 |
| 健康 | `healthy` | 任务按时正常完成 |

**`warning` 判断逻辑：**

基于 cron 表达式推算出下次应执行时间，加上宽限期后仍未完成则标记为警告，而不是简单以上次完成时间为基准。

```
下次预期执行时间（by cron）+ grace_time_in_minutes < 现在 → warning
```

## 日志类型说明

| 类型 | 说明 |
|------|------|
| `starting` | 任务开始执行 |
| `finished` | 任务成功完成 |
| `failed` | 任务执行失败 |
| `skipped` | 任务被跳过 |

## 日志自动清理

`spatie/laravel-schedule-monitor` 的日志模型使用了 Laravel `MassPrunable`，默认保留 **30 天**。

可在业务项目的 `config/schedule-monitor.php` 中修改保留天数：

```php
'delete_log_items_older_than_days' => 30,
```

需要在定时任务中注册清理命令，否则日志不会自动清除：

```php
// routes/console.php 或 app/Console/Kernel.php
Schedule::command('model:prune')->daily();
```

## License

MIT
