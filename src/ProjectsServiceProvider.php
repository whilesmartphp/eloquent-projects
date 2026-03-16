<?php

namespace Whilesmart\Projects;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ProjectsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/projects.php', 'projects');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/projects.php' => config_path('projects.php'),
        ], 'projects-config');

        if (config('projects.register_routes', true)) {
            $this->registerRoutes();
        }
    }

    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => config('projects.route_prefix', 'api'),
            'middleware' => config('projects.route_middleware', ['auth:sanctum']),
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/projects.php');
        });
    }
}
