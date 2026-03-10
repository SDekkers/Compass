<?php

declare(strict_types=1);

use Compass\Extractors\MiddlewareExtractor;

beforeEach(function (): void {
    $this->extractor = new MiddlewareExtractor();
});

it('detects passport security scheme', function (): void {
    $routes = [['middleware' => ['auth:api']]];
    $schemes = $this->extractor->extractSecuritySchemes($routes);

    expect($schemes)->toHaveKey('oauth2');
    expect($schemes['oauth2']['type'])->toBe('oauth2');
});

it('detects sanctum security scheme', function (): void {
    $routes = [['middleware' => ['auth:sanctum']]];
    $schemes = $this->extractor->extractSecuritySchemes($routes);

    expect($schemes)->toHaveKey('bearerAuth');
    expect($schemes['bearerAuth']['scheme'])->toBe('bearer');
});

it('extracts route-level security for passport', function (): void {
    $route = ['middleware' => ['auth:api']];
    $security = $this->extractor->extractRouteSecurity($route);

    expect($security)->toBe([['oauth2' => []]]);
});

it('returns empty security for unauthenticated routes', function (): void {
    $route = ['middleware' => ['throttle:60,1']];
    $security = $this->extractor->extractRouteSecurity($route);

    expect($security)->toBe([]);
});

it('extracts permission from permission middleware', function (): void {
    $route = ['middleware' => ['permission:edr.access.access']];
    $result = $this->extractor->extractRoutePermissions($route);

    expect($result['permissions'])->toBe(['edr.access.access']);
    expect($result['licenses'])->toBe([]);
    expect($result['scopes'])->toBe([]);
});

it('extracts license from license.access middleware', function (): void {
    $route = ['middleware' => ['license.access:edr']];
    $result = $this->extractor->extractRoutePermissions($route);

    expect($result['permissions'])->toBe([]);
    expect($result['licenses'])->toBe(['edr']);
    expect($result['scopes'])->toBe([]);
});

it('extracts scope from scope middleware', function (): void {
    $route = ['middleware' => ['auth:api', 'scope:read-alerts']];
    $result = $this->extractor->extractRoutePermissions($route);

    expect($result['permissions'])->toBe([]);
    expect($result['licenses'])->toBe([]);
    expect($result['scopes'])->toBe(['read-alerts']);
});

it('extracts scopes from scopes middleware with multiple values', function (): void {
    $route = ['middleware' => ['auth:api', 'scopes:read-alerts,write-alerts']];
    $result = $this->extractor->extractRoutePermissions($route);

    expect($result['scopes'])->toBe(['read-alerts', 'write-alerts']);
});

it('populates oauth2 scopes in security schemes from scope middleware', function (): void {
    $routes = [
        ['middleware' => ['auth:api', 'scope:read-alerts']],
        ['middleware' => ['auth:api', 'scopes:write-alerts,read-alerts']],
    ];
    $schemes = $this->extractor->extractSecuritySchemes($routes);

    expect($schemes)->toHaveKey('oauth2');
    $scopes = $schemes['oauth2']['flows']['clientCredentials']['scopes'];
    expect($scopes)->toBeInstanceOf(\ArrayObject::class);
    expect(isset($scopes['read-alerts']))->toBeTrue();
    expect(isset($scopes['write-alerts']))->toBeTrue();
});

it('extracts all permission types from multiple middleware', function (): void {
    $route = ['middleware' => ['auth:api', 'permission:edr.access.access', 'license.access:edr', 'scope:read-alerts']];
    $result = $this->extractor->extractRoutePermissions($route);

    expect($result['permissions'])->toBe(['edr.access.access']);
    expect($result['licenses'])->toBe(['edr']);
    expect($result['scopes'])->toBe(['read-alerts']);
});

it('returns empty arrays when no permission or scope middleware present', function (): void {
    $route = ['middleware' => ['throttle:60,1', 'auth:api']];
    $result = $this->extractor->extractRoutePermissions($route);

    expect($result['permissions'])->toBe([]);
    expect($result['licenses'])->toBe([]);
    expect($result['scopes'])->toBe([]);
});

it('includes scopes in route security for passport routes with scope middleware', function (): void {
    $route = ['middleware' => ['auth:api', 'scope:read-alerts']];
    $security = $this->extractor->extractRouteSecurity($route);

    expect($security)->toBe([['oauth2' => ['read-alerts']]]);
});
