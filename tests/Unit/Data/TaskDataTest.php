<?php

use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\TaskData;

test('TaskData can be created from array', function () {
    $data = TaskData::from([
        'gid' => '123',
        'resource_type' => 'task',
        'name' => 'Test Task',
        'completed' => false,
    ]);

    expect($data->gid)->toBe('123')
        ->and($data->resource_type)->toBe('task')
        ->and($data->name)->toBe('Test Task')
        ->and($data->completed)->toBeFalse();
});

test('TaskData handles null optional fields', function () {
    $data = TaskData::from(['gid' => '123']);

    expect($data->gid)->toBe('123')
        ->and($data->name)->toBeNull()
        ->and($data->assignee)->toBeNull()
        ->and($data->completed)->toBeNull()
        ->and($data->due_on)->toBeNull()
        ->and($data->notes)->toBeNull()
        ->and($data->tags)->toBeNull()
        ->and($data->projects)->toBeNull();
});

test('TaskData casts nested assignee to CompactResource', function () {
    $data = TaskData::from([
        'gid' => '123',
        'assignee' => ['gid' => '456', 'name' => 'John', 'resource_type' => 'user'],
    ]);

    expect($data->assignee)->toBeInstanceOf(CompactResource::class)
        ->and($data->assignee->gid)->toBe('456')
        ->and($data->assignee->name)->toBe('John')
        ->and($data->assignee->resource_type)->toBe('user');
});

test('TaskData casts nested parent to CompactResource', function () {
    $data = TaskData::from([
        'gid' => '123',
        'parent' => ['gid' => '789', 'name' => 'Parent Task', 'resource_type' => 'task'],
    ]);

    expect($data->parent)->toBeInstanceOf(CompactResource::class)
        ->and($data->parent->gid)->toBe('789')
        ->and($data->parent->name)->toBe('Parent Task');
});

test('TaskData casts nested workspace to CompactResource', function () {
    $data = TaskData::from([
        'gid' => '123',
        'workspace' => ['gid' => '999', 'name' => 'My Workspace', 'resource_type' => 'workspace'],
    ]);

    expect($data->workspace)->toBeInstanceOf(CompactResource::class)
        ->and($data->workspace->gid)->toBe('999');
});

test('TaskData handles full payload', function () {
    $data = TaskData::from([
        'gid' => '123',
        'resource_type' => 'task',
        'name' => 'Full Task',
        'completed' => true,
        'completed_at' => '2024-01-01T12:00:00.000Z',
        'created_at' => '2024-01-01T00:00:00.000Z',
        'due_on' => '2024-02-01',
        'start_on' => '2024-01-15',
        'notes' => 'Some notes',
        'permalink_url' => 'https://app.asana.com/0/123/456',
    ]);

    expect($data->gid)->toBe('123')
        ->and($data->completed)->toBeTrue()
        ->and($data->completed_at)->toBe('2024-01-01T12:00:00.000Z')
        ->and($data->due_on)->toBe('2024-02-01')
        ->and($data->notes)->toBe('Some notes')
        ->and($data->permalink_url)->toBe('https://app.asana.com/0/123/456');
});
