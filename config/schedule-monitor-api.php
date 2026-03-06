<?php

return [
    /*
     * The route prefix for all schedule monitor API endpoints.
     */
    'route_prefix' => 'api/v1/system/schedule-tasks',

    /*
     * The route name prefix.
     */
    'route_name' => 'system.schedule-tasks',

    /*
     * Middleware applied to all routes.
     */
    'middleware' => ['auth:sanctum'],

    /*
     * Default items per page for task list.
     */
    'per_page' => 20,

    /*
     * Default items per page for task logs.
     */
    'log_per_page' => 50,
];
