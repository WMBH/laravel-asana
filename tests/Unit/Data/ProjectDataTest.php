<?php

use WMBH\Asana\Data\ProjectData;
use WMBH\Asana\Data\Shared\CompactResource;

test('ProjectData can be created from array', function () {
    $data = ProjectData::from([
        'gid' => '789',
        'resource_type' => 'project',
        'name' => 'Test Project',
        'archived' => false,
    ]);

    expect($data->gid)->toBe('789')
        ->and($data->resource_type)->toBe('project')
        ->and($data->name)->toBe('Test Project')
        ->and($data->archived)->toBeFalse();
});

test('ProjectData handles null optional fields', function () {
    $data = ProjectData::from(['gid' => '789']);

    expect($data->gid)->toBe('789')
        ->and($data->name)->toBeNull()
        ->and($data->archived)->toBeNull()
        ->and($data->color)->toBeNull()
        ->and($data->owner)->toBeNull()
        ->and($data->team)->toBeNull()
        ->and($data->notes)->toBeNull();
});

test('ProjectData casts nested owner to CompactResource', function () {
    $data = ProjectData::from([
        'gid' => '789',
        'owner' => ['gid' => '456', 'name' => 'Jane', 'resource_type' => 'user'],
    ]);

    expect($data->owner)->toBeInstanceOf(CompactResource::class)
        ->and($data->owner->gid)->toBe('456')
        ->and($data->owner->name)->toBe('Jane');
});

test('ProjectData casts nested team to CompactResource', function () {
    $data = ProjectData::from([
        'gid' => '789',
        'team' => ['gid' => '111', 'name' => 'Engineering', 'resource_type' => 'team'],
    ]);

    expect($data->team)->toBeInstanceOf(CompactResource::class)
        ->and($data->team->gid)->toBe('111')
        ->and($data->team->name)->toBe('Engineering');
});

test('ProjectData casts nested workspace to CompactResource', function () {
    $data = ProjectData::from([
        'gid' => '789',
        'workspace' => ['gid' => '999', 'name' => 'My Workspace', 'resource_type' => 'workspace'],
    ]);

    expect($data->workspace)->toBeInstanceOf(CompactResource::class)
        ->and($data->workspace->gid)->toBe('999');
});

test('ProjectData handles full payload', function () {
    $data = ProjectData::from([
        'gid' => '789',
        'resource_type' => 'project',
        'name' => 'Full Project',
        'archived' => false,
        'color' => 'light-green',
        'created_at' => '2024-01-01T00:00:00.000Z',
        'due_on' => '2024-06-01',
        'start_on' => '2024-01-01',
        'notes' => 'Project notes',
        'public' => true,
        'permalink_url' => 'https://app.asana.com/0/789',
    ]);

    expect($data->gid)->toBe('789')
        ->and($data->archived)->toBeFalse()
        ->and($data->color)->toBe('light-green')
        ->and($data->due_on)->toBe('2024-06-01')
        ->and($data->notes)->toBe('Project notes')
        ->and($data->public)->toBeTrue();
});
