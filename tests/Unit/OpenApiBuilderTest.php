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

it('generates unique operationIds by prepending http method', function (): void {
    $this->builder->addPath(
        path: '/api/users/{user}',
        method: 'put',
        summary: 'users.update',
        group: 'Users',
        responses: ['200' => ['description' => 'OK']],
    );

    $this->builder->addPath(
        path: '/api/users/{user}',
        method: 'patch',
        summary: 'users.update',
        group: 'Users',
        responses: ['200' => ['description' => 'OK']],
    );

    $spec = $this->builder->build();

    expect($spec['paths']['/api/users/{user}']['put']['operationId'])->toBe('put.users.update');
    expect($spec['paths']['/api/users/{user}']['patch']['operationId'])->toBe('patch.users.update');
});

it('outputs response status codes as strings in yaml', function (): void {
    $this->builder->addPath(
        path: '/api/users',
        method: 'get',
        summary: 'List users',
        group: 'Users',
        responses: [200 => ['description' => 'OK'], 404 => ['description' => 'Not Found']],
    );

    $spec = $this->builder->build();
    $yaml = \Symfony\Component\Yaml\Yaml::dump($spec, 10, 2, \Symfony\Component\Yaml\Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE | \Symfony\Component\Yaml\Yaml::DUMP_NUMERIC_KEY_AS_STRING);

    expect($yaml)->toContain("'200':");
    expect($yaml)->toContain("'404':");
    expect($yaml)->not->toMatch('/^\s+200:/m');
});

it('adds application/json content type to responses', function (): void {
    $this->builder->addPath(
        path: '/api/users',
        method: 'get',
        summary: 'List users',
        group: 'Users',
        responses: ['200' => ['description' => 'OK']],
    );

    $spec = $this->builder->build();
    $response = $spec['paths']['/api/users']['get']['responses']['200'];

    expect($response['content'])->toHaveKey('application/json');
    expect($response['content']['application/json']['schema']['type'])->toBe('object');
});

it('does not overwrite existing content type on responses', function (): void {
    $this->builder->addPath(
        path: '/api/users',
        method: 'get',
        summary: 'List users',
        group: 'Users',
        responses: ['200' => ['description' => 'OK', 'content' => ['text/plain' => ['schema' => ['type' => 'string']]]]],
    );

    $spec = $this->builder->build();
    $response = $spec['paths']['/api/users']['get']['responses']['200'];

    expect($response['content'])->toHaveKey('text/plain');
    expect($response['content'])->not->toHaveKey('application/json');
});

it('adds security schemes', function (): void {
    $this->builder->addSecuritySchemes([
        'bearerAuth' => ['type' => 'http', 'scheme' => 'bearer'],
    ]);

    $spec = $this->builder->build();

    expect($spec['components']['securitySchemes'])->toHaveKey('bearerAuth');
});
