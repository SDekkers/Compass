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
