<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    $outputPath = sys_get_temp_dir() . '/compass-test';
    if (is_dir($outputPath)) {
        array_map('unlink', glob("{$outputPath}/*") ?: []);
    }
});

it('generates openapi files via artisan command', function (): void {
    Route::get('api/users', ['uses' => 'Compass\\Tests\\Fixtures\\UserController', 'middleware' => ['auth:api']]);
    Route::post('api/users', ['uses' => 'Compass\\Tests\\Fixtures\\UserController', 'middleware' => ['auth:api']]);

    $this->artisan('compass:generate')
        ->expectsOutputToContain('Done!')
        ->assertSuccessful();

    $outputPath = sys_get_temp_dir() . '/compass-test';
    expect(file_exists("{$outputPath}/openapi.json"))->toBeTrue();
    expect(file_exists("{$outputPath}/openapi.yaml"))->toBeTrue();

    $spec = json_decode(file_get_contents("{$outputPath}/openapi.json"), true);
    expect($spec['openapi'])->toBe('3.1.0');
    expect($spec['paths'])->toHaveKey('/api/users');
});
