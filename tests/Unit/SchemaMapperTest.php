<?php

declare(strict_types=1);

use Compass\Schema\SchemaMapper;

beforeEach(function (): void {
    $this->mapper = new SchemaMapper();
});

it('maps string rule', function (): void {
    $result = $this->mapper->map(['string']);
    expect($result)->toBe(['type' => 'string']);
});

it('maps integer rule', function (): void {
    $result = $this->mapper->map(['integer']);
    expect($result)->toBe(['type' => 'integer']);
});

it('maps boolean rule', function (): void {
    $result = $this->mapper->map(['boolean']);
    expect($result)->toBe(['type' => 'boolean']);
});

it('maps email rule', function (): void {
    $result = $this->mapper->map(['email']);
    expect($result)->toBe(['type' => 'string', 'format' => 'email']);
});

it('maps uuid rule', function (): void {
    $result = $this->mapper->map(['uuid']);
    expect($result)->toBe(['type' => 'string', 'format' => 'uuid']);
});

it('maps date rule', function (): void {
    $result = $this->mapper->map(['date']);
    expect($result)->toBe(['type' => 'string', 'format' => 'date']);
});

it('maps nullable rule', function (): void {
    $result = $this->mapper->map(['string', 'nullable']);
    expect($result)->toBe(['type' => ['string', 'null']]);
});

it('maps max rule for string', function (): void {
    $result = $this->mapper->map(['string', 'max:255']);
    expect($result)->toBe(['type' => 'string', 'maxLength' => 255]);
});

it('maps min rule for integer', function (): void {
    $result = $this->mapper->map(['integer', 'min:1']);
    expect($result)->toBe(['type' => 'integer', 'minimum' => 1]);
});

it('maps in rule to enum', function (): void {
    $result = $this->mapper->map(['in:a,b,c']);
    expect($result['enum'])->toBe(['a', 'b', 'c']);
    expect($result['type'])->toBe('string');
});

it('maps array rule', function (): void {
    $result = $this->mapper->map(['array']);
    expect($result)->toBe(['type' => 'array']);
});

it('maps between rule for string', function (): void {
    $result = $this->mapper->map(['string', 'between:3,50']);
    expect($result)->toBe(['type' => 'string', 'minLength' => 3, 'maxLength' => 50]);
});

it('maps combined rules', function (): void {
    $result = $this->mapper->map(['string', 'max:255', 'nullable']);
    expect($result)->toBe(['type' => ['string', 'null'], 'maxLength' => 255]);
});

it('defaults to string when no type rule given', function (): void {
    $result = $this->mapper->map(['required', 'max:100']);
    expect($result['type'])->toBe('string');
});
