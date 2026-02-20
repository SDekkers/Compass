<?php

declare(strict_types=1);

namespace Compass\Extractors;

final class MiddlewareExtractor
{
    public function extractSecuritySchemes(array $routes): array
    {
        $schemes = [];

        foreach ($routes as $route) {
            $middleware = $route['middleware'] ?? [];

            foreach ($middleware as $mw) {
                if ($this->isPassportMiddleware($mw)) {
                    $schemes['oauth2'] = [
                        'type' => 'oauth2',
                        'flows' => [
                            'clientCredentials' => [
                                'tokenUrl' => '/oauth/token',
                                'scopes' => new \stdClass(),
                            ],
                        ],
                    ];
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
            }
        }

        return $schemes;
    }

    public function extractRouteSecurity(array $route): array
    {
        $middleware = $route['middleware'] ?? [];

        foreach ($middleware as $mw) {
            if ($this->isPassportMiddleware($mw)) {
                return [['oauth2' => []]];
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

    public function isAuthMiddleware(string $middleware): bool
    {
        return $this->isPassportMiddleware($middleware)
            || $this->isSanctumMiddleware($middleware)
            || $this->isBearerMiddleware($middleware);
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
}
