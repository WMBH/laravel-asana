<?php

use WMBH\Asana\Data\EventData;
use WMBH\Asana\Data\Shared\CompactResource;

test('EventData can be created from array', function () {
    $data = EventData::from([
        'type' => 'task',
        'action' => 'changed',
        'created_at' => '2025-01-01T00:00:00.000Z',
        'change' => ['field' => 'name', 'action' => 'changed', 'new_value' => 'Renamed'],
    ]);

    expect($data->type)->toBe('task')
        ->and($data->action)->toBe('changed')
        ->and($data->created_at)->toBe('2025-01-01T00:00:00.000Z')
        ->and($data->change)->toBe(['field' => 'name', 'action' => 'changed', 'new_value' => 'Renamed']);
});

test('EventData has no required fields', function () {
    $data = EventData::from([]);

    expect($data->user)->toBeNull()
        ->and($data->resource)->toBeNull()
        ->and($data->type)->toBeNull()
        ->and($data->action)->toBeNull()
        ->and($data->parent)->toBeNull()
        ->and($data->created_at)->toBeNull()
        ->and($data->change)->toBeNull();
});

test('EventData casts nested user, resource and parent to CompactResource', function () {
    $data = EventData::from([
        'user' => ['gid' => '111', 'name' => 'Jane', 'resource_type' => 'user'],
        'resource' => ['gid' => '222', 'name' => 'Task', 'resource_type' => 'task'],
        'parent' => ['gid' => '333', 'name' => 'Project', 'resource_type' => 'project'],
    ]);

    expect($data->user)->toBeInstanceOf(CompactResource::class)
        ->and($data->user->gid)->toBe('111')
        ->and($data->resource)->toBeInstanceOf(CompactResource::class)
        ->and($data->resource->gid)->toBe('222')
        ->and($data->parent)->toBeInstanceOf(CompactResource::class)
        ->and($data->parent->gid)->toBe('333');
});
