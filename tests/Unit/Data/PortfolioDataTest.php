<?php

use WMBH\Asana\Data\PortfolioData;
use WMBH\Asana\Data\Shared\CompactResource;

test('PortfolioData can be created from array', function () {
    $data = PortfolioData::from([
        'gid' => '1000',
        'resource_type' => 'portfolio',
        'name' => 'Q1 Projects',
        'color' => 'light-green',
    ]);

    expect($data->gid)->toBe('1000')
        ->and($data->name)->toBe('Q1 Projects')
        ->and($data->color)->toBe('light-green');
});

test('PortfolioData handles null optional fields', function () {
    $data = PortfolioData::from(['gid' => '1000']);

    expect($data->gid)->toBe('1000')
        ->and($data->name)->toBeNull()
        ->and($data->color)->toBeNull()
        ->and($data->owner)->toBeNull()
        ->and($data->workspace)->toBeNull()
        ->and($data->members)->toBeNull()
        ->and($data->custom_fields)->toBeNull();
});

test('PortfolioData casts nested owner to CompactResource', function () {
    $data = PortfolioData::from([
        'gid' => '1000',
        'owner' => ['gid' => '111', 'name' => 'Jane', 'resource_type' => 'user'],
    ]);

    expect($data->owner)->toBeInstanceOf(CompactResource::class)
        ->and($data->owner->gid)->toBe('111');
});

test('PortfolioData casts nested workspace to CompactResource', function () {
    $data = PortfolioData::from([
        'gid' => '1000',
        'workspace' => ['gid' => '1', 'name' => 'WS', 'resource_type' => 'workspace'],
    ]);

    expect($data->workspace)->toBeInstanceOf(CompactResource::class)
        ->and($data->workspace->gid)->toBe('1');
});

test('PortfolioData casts nested created_by to CompactResource', function () {
    $data = PortfolioData::from([
        'gid' => '1000',
        'created_by' => ['gid' => '222', 'name' => 'Creator', 'resource_type' => 'user'],
    ]);

    expect($data->created_by)->toBeInstanceOf(CompactResource::class)
        ->and($data->created_by->gid)->toBe('222');
});
