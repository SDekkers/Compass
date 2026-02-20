<?php

declare(strict_types=1);

namespace Compass;

use Compass\Http\CompassController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class CompassServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/compass.php', 'compass');

        $this->app->singleton(CompassGenerator::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/compass.php' => config_path('compass.php'),
            ], 'compass-config');

            $this->commands([
                Commands\GenerateCommand::class,
            ]);
        }

        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        if (! config('compass.ui.enabled', true)) {
            return;
        }

        $path = config('compass.ui.path', 'docs');
        $middleware = config('compass.ui.middleware', []);

        Route::middleware($middleware)
            ->group(function () use ($path): void {
                Route::get($path, CompassController::class)->name('compass.ui');
                Route::get("{$path}/openapi.json", [CompassController::class, 'spec'])->name('compass.spec');
            });
    }
}
