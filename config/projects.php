<?php

use Whilesmart\Projects\Models\Project;
use Whilesmart\Projects\Models\ProjectAssignment;
use Whilesmart\Projects\Models\ProjectStatus;

return [
    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    |
    | Override these to use your own extended models.
    |
    */
    'models' => [
        'project' => Project::class,
        'project_status' => ProjectStatus::class,
        'project_assignment' => ProjectAssignment::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    */
    'register_routes' => env('PROJECTS_REGISTER_ROUTES', true),
    'route_prefix' => env('PROJECTS_ROUTE_PREFIX', ''),
    'route_middleware' => ['auth:sanctum'],

    /*
    |--------------------------------------------------------------------------
    | Middleware Hooks
    |--------------------------------------------------------------------------
    |
    | Register hook classes that implement MiddlewareHookInterface.
    | Hooks run before/after controller actions to customize behavior.
    |
    */
    'middleware_hooks' => [
        // App\Hooks\ProjectHook::class,
    ],
];
