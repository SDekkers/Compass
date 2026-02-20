<?php

declare(strict_types=1);

use Compass\Extractors\ResponseExtractor;
use Compass\Tests\Fixtures\UserResource;

it('finds resource class from controller return type', function (): void {
    $extractor = app(ResponseExtractor::class);
    $result = $extractor->findResourceClass('Compass\\Tests\\Fixtures\\UserController');

    expect($result)->toBe(UserResource::class);
});

it('returns null when no resource return type', function (): void {
    $extractor = app(ResponseExtractor::class);
    $result = $extractor->findResourceClass('Compass\\Tests\\Fixtures\\ListController');

    // ListController does return UserResource, so this should find it
    expect($result)->toBe(UserResource::class);
});

it('extracts schema keys from resource toArray', function (): void {
    $extractor = app(ResponseExtractor::class);
    $schema = $extractor->extractResourceSchema(UserResource::class);

    expect($schema)->not->toBeNull();
    expect($schema['type'])->toBe('object');
    expect($schema['properties'])->toHaveKeys(['id', 'name', 'email', 'created_at']);
});
