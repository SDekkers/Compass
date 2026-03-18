<?php

declare(strict_types=1);

namespace Compass\Extractors;

final class MiddlewareExtractor
{
    public function extractSecuritySchemes(array $routes): array
    {
        $schemes = [];
        $allScopes = [];

        foreach ($routes as $route) {
            $middleware = $route['middleware'] ?? [];

            foreach ($middleware as $mw) {
                if ($this->isPassportMiddleware($mw)) {
                    if (! isset($schemes['oauth2'])) {
                        $schemes['oauth2'] = [
                            'type' => 'oauth2',
                            'flows' => [
                                'clientCredentials' => [
                                    'tokenUrl' => '/oauth/token',
                                    'scopes' => new \ArrayObject(),
                                ],
                            ],
                        ];
                    }
                }

                if ($this->isSanctumMiddleware($mw)) {
                    $schemes['bearerAuth'] = [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'Token',
                    ];
                }

                if ($this->isBearerMiddleware($mw)) {
                    $schemes['bearerAuth'] = [
                        'type' => 'http',
                        'scheme' => 'bearer',
                    ];
                }

                $routeScopes = $this->parseScopesFromMiddleware($mw);
                foreach ($routeScopes as $scope) {
                    $allScopes[$scope] = $scope;
                }
            }
        }

        if ($allScopes !== [] && isset($schemes['oauth2'])) {
            $scopeObjects = new \ArrayObject();
            foreach ($allScopes as $scope) {
                $scopeObjects[$scope] = '';
            }
            $schemes['oauth2']['flows']['clientCredentials']['scopes'] = $scopeObjects;
        }

        return $schemes;
    }

    public function extractRouteSecurity(array $route): array
    {
        $middleware = $route['middleware'] ?? [];

        foreach ($middleware as $mw) {
            if ($this->isPassportMiddleware($mw)) {
                $scopes = $this->extractRoutePermissions($route)['scopes'];

                return [['oauth2' => $scopes]];
            }

            if ($this->isSanctumMiddleware($mw)) {
                return [['bearerAuth' => []]];
            }

            if ($this->isBearerMiddleware($mw)) {
                return [['bearerAuth' => []]];
            }
        }

        return [];
    }

    public function extractRoutePermissions(array $route): array
    {
        $middleware = $route['middleware'] ?? [];

        $permissions = [];
        $licenses = [];
        $scopes = [];
        $customMiddleware = [];

        foreach ($middleware as $mw) {
            if (str_starts_with($mw, 'permission:')) {
                $value = substr($mw, strlen('permission:'));
                foreach (explode(',', $value) as $permission) {
                    $permission = trim($permission);
                    if ($permission !== '') {
                        $permissions[] = $permission;
                    }
                }
            } elseif (str_starts_with($mw, 'license.access:')) {
                $value = substr($mw, strlen('license.access:'));
                foreach (explode(',', $value) as $license) {
                    $license = trim($license);
                    if ($license !== '') {
                        $licenses[] = $license;
                    }
                }
            } elseif ($this->isCustomMiddleware($mw)) {
                $customMiddleware[] = $mw;
            } else {
                foreach ($this->parseScopesFromMiddleware($mw) as $scope) {
                    $scopes[] = $scope;
                }
            }
        }

        return [
            'permissions' => $permissions,
            'licenses' => $licenses,
            'scopes' => $scopes,
            'middleware' => $customMiddleware,
        ];
    }

    public function isAuthMiddleware(string $middleware): bool
    {
        return $this->isPassportMiddleware($middleware)
            || $this->isSanctumMiddleware($middleware)
            || $this->isBearerMiddleware($middleware);
    }

    private function parseScopesFromMiddleware(string $middleware): array
    {
        $prefixes = ['scope:', 'scopes:', 'client.action:'];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($middleware, $prefix)) {
                $value = substr($middleware, strlen($prefix));
                $scopes = [];
                foreach (explode(',', $value) as $scope) {
                    $scope = trim($scope);
                    if ($scope !== '') {
                        $scopes[] = $scope;
                    }
                }

                return $scopes;
            }
        }

        return [];
    }

    private function isPassportMiddleware(string $middleware): bool
    {
        return str_contains($middleware, 'passport')
            || $middleware === 'auth:api'
            || str_contains($middleware, 'Laravel\\Passport');
    }

    private function isSanctumMiddleware(string $middleware): bool
    {
        return str_contains($middleware, 'sanctum')
            || $middleware === 'auth:sanctum'
            || str_contains($middleware, 'Laravel\\Sanctum');
    }

    private function isBearerMiddleware(string $middleware): bool
    {
        return $middleware === 'auth'
            || $middleware === 'auth:bearer';
    }

    private function isCustomMiddleware(string $middleware): bool
    {
        // Skip framework / well-known middleware — everything else is custom
        $ignoredPrefixes = ['auth', 'throttle', 'permission:', 'license.access:', 'scope:', 'scopes:', 'client.action:', 'bindings', 'can:', 'verified', 'signed', 'cache.headers:', 'precognitive'];

        foreach ($ignoredPrefixes as $prefix) {
            if (str_starts_with($middleware, $prefix)) {
                return false;
            }
        }

        // Also skip bare framework middleware class names
        if (str_contains($middleware, 'Illuminate\\') || str_contains($middleware, 'Laravel\\')) {
            return false;
        }

        // Skip common single-word framework middleware
        $ignoredExact = ['api', 'web', 'guest', 'password.confirm', 'SubstituteBindings'];
        if (in_array($middleware, $ignoredExact, true)) {
            return false;
        }

        return true;
    }
}
