<?php

declare(strict_types=1);

namespace Compass\Extractors;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

final class RouteExtractor
{
    public function __construct(
        private readonly Router $router,
    ) {}

    public function extract(): array
    {
        $routes = [];
        $prefixes = config('compass.routes.prefixes', ['api']);
        $exclude = config('compass.routes.exclude', []);
        $excludePatterns = config('compass.routes.exclude_patterns', []);

        foreach ($this->router->getRoutes() as $route) {
            /** @var Route $route */
            $uri = $route->uri();

            if (! $this->matchesPrefix($uri, $prefixes)) {
                continue;
            }

            if (in_array($uri, $exclude, true)) {
                continue;
            }

            if ($this->matchesPattern($uri, $excludePatterns)) {
                continue;
            }

            foreach ($route->methods() as $method) {
                if ($method === 'HEAD') {
                    continue;
                }

                $controller = $route->getActionName();
                $routes[] = [
                    'method' => $method,
                    'uri' => '/' . ltrim($uri, '/'),
                    'name' => $route->getName(),
                    'controller' => $controller,
                    'middleware' => $route->gatherMiddleware(),
                    'group' => $this->resolveGroup($controller),
                    'parameters' => $route->parameterNames(),
                ];
            }
        }

        return $routes;
    }

    public function resolveGroup(string $controller): string
    {
        $overrides = config('compass.grouping.overrides', []);
        if (isset($overrides[$controller])) {
            return $overrides[$controller];
        }

        if (! config('compass.grouping.enabled', true)) {
            return 'General';
        }

        // Match App\Modules\{Group}\Controllers\{Subgroup}\Controller (subgroup is not a controller class)
        if (preg_match('/Modules\\\\([^\\\\]+)\\\\.*?Controllers\\\\([^\\\\]+)\\\\/', $controller, $matches)) {
            return $matches[1] . ' > ' . $matches[2];
        }

        // Match App\Modules\{Group}\...
        if (preg_match('/Modules\\\\([^\\\\]+)\\\\/', $controller, $matches)) {
            return $matches[1];
        }

        // Match App\Http\Controllers\{Group}\Controller
        if (preg_match('/Controllers\\\\([^\\\\]+)\\\\/', $controller, $matches)) {
            return $matches[1];
        }

        return 'General';
    }

    private function matchesPrefix(string $uri, array $prefixes): bool
    {
        if ($prefixes === [] || $prefixes === ['*']) {
            return true;
        }

        foreach ($prefixes as $prefix) {
            if (str_starts_with($uri, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function matchesPattern(string $uri, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (fnmatch($pattern, $uri)) {
                return true;
            }
        }

        return false;
    }
}
