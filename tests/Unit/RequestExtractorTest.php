<?php

declare(strict_types=1);

use Compass\Extractors\RequestExtractor;
use Compass\Tests\Fixtures\StoreUserRequest;

it('finds FormRequest from controller action', function (): void {
    $extractor = app(RequestExtractor::class);
    $result = $extractor->findFormRequest('Compass\\Tests\\Fixtures\\UserController');

    expect($result)->toBe(StoreUserRequest::class);
});

it('returns null when no FormRequest found', function (): void {
    $extractor = app(RequestExtractor::class);
    $result = $extractor->findFormRequest('Compass\\Tests\\Fixtures\\ListController');

    expect($result)->toBeNull();
});

it('extracts rules from FormRequest', function (): void {
    $extractor = app(RequestExtractor::class);
    $rules = $extractor->getRules(StoreUserRequest::class);

    expect($rules)->toHaveKeys(['name', 'email', 'age', 'role', 'uuid']);
});

it('builds request body schema for POST route', function (): void {
    $extractor = app(RequestExtractor::class);

    $route = [
        'method' => 'POST',
        'controller' => 'Compass\\Tests\\Fixtures\\UserController',
    ];

    $result = $extractor->extract($route);

    expect($result)->not->toBeNull();
    expect($result)->toHaveKey('body');

    $schema = $result['body']['content']['application/json']['schema'];
    expect($schema['properties'])->toHaveKeys(['name', 'email', 'age', 'role', 'uuid']);
    expect($schema['required'])->toContain('name', 'email', 'role', 'uuid');
});

it('builds query parameters for GET route', function (): void {
    $extractor = app(RequestExtractor::class);

    $route = [
        'method' => 'GET',
        'controller' => 'Compass\\Tests\\Fixtures\\UserController',
    ];

    $result = $extractor->extract($route);

    expect($result)->not->toBeNull();
    expect($result)->toHaveKey('parameters');
    expect($result['parameters'])->toHaveCount(5);
});
