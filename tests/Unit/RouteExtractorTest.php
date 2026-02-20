<?php

declare(strict_types=1);

use Compass\Extractors\RouteExtractor;

it('resolves module group from namespace', function (): void {
    $extractor = app(RouteExtractor::class);

    expect($extractor->resolveGroup('App\\Modules\\CyberGuard\\Controllers\\Detections\\IndexController'))
        ->toBe('CyberGuard > Detections');

    expect($extractor->resolveGroup('App\\Modules\\Users\\Controllers\\ShowController'))
        ->toBe('Users');

    expect($extractor->resolveGroup('App\\Http\\Controllers\\Api\\AuthController'))
        ->toBe('Api');

    expect($extractor->resolveGroup('App\\Http\\Controllers\\HomeController'))
        ->toBe('General');
});

it('extracts routes with api prefix', function (): void {
    $router = app('router');
    $router->get('api/users', ['uses' => 'App\\Http\\Controllers\\UserController@index']);
    $router->get('web/home', ['uses' => 'App\\Http\\Controllers\\HomeController@index']);

    $extractor = app(RouteExtractor::class);
    $routes = $extractor->extract();

    $uris = array_column($routes, 'uri');
    expect($uris)->toContain('/api/users');
    expect($uris)->not->toContain('/web/home');
});
