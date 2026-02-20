<?php

declare(strict_types=1);

use Compass\Schema\OpenApiBuilder;

beforeEach(function (): void {
    $this->builder = new OpenApiBuilder();
    $this->builder->init('Test API', '1.0.0', 'Test description', [
        ['url' => 'https://api.example.com', 'description' => 'Production'],
    ]);
});

it('builds valid openapi structure', function (): void {
    $spec = $this->builder->build();

    expect($spec['openapi'])->toBe('3.1.0');
    expect($spec['info']['title'])->toBe('Test API');
    expect($spec['info']['version'])->toBe('1.0.0');
    expect($spec['info']['description'])->toBe('Test description');
    expect($spec['servers'])->toHaveCount(1);
});

it('adds paths with tags', function (): void {
    $this->builder->addPath(
        path: '/api/users',
        method: 'get',
        summary: 'List users',
        group: 'Users',
        responses: ['200' => ['description' => 'OK']],
    );

    $spec = $this->builder->build();

    expect($spec['paths'])->toHaveKey('/api/users');
    expect($spec['paths']['/api/users']['get']['tags'])->toBe(['Users']);
    expect($spec['tags'][0]['name'])->toBe('Users');
});

it('converts laravel route params to openapi format', function (): void {
    $this->builder->addPath(
        path: '/api/users/{user}',
        method: 'get',
        summary: 'Show user',
        group: 'Users',
        responses: ['200' => ['description' => 'OK']],
    );

    $spec = $this->builder->build();

    expect($spec['paths'])->toHaveKey('/api/users/{user}');
    $params = $spec['paths']['/api/users/{user}']['get']['parameters'];
    expect($params[0]['name'])->toBe('user');
    expect($params[0]['in'])->toBe('path');
    expect($params[0]['required'])->toBeTrue();
});

it('adds security schemes', function (): void {
    $this->builder->addSecuritySchemes([
        'bearerAuth' => ['type' => 'http', 'scheme' => 'bearer'],
    ]);

    $spec = $this->builder->build();

    expect($spec['components']['securitySchemes'])->toHaveKey('bearerAuth');
});
