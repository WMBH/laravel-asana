<?php

use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\UserTaskListData;

test('UserTaskListData can be created from array', function () {
    $data = UserTaskListData::from([
        'gid' => '1100',
        'resource_type' => 'user_task_list',
        'name' => 'My Tasks',
    ]);

    expect($data->gid)->toBe('1100')
        ->and($data->resource_type)->toBe('user_task_list')
        ->and($data->name)->toBe('My Tasks');
});

test('UserTaskListData handles null optional fields', function () {
    $data = UserTaskListData::from(['gid' => '1100']);

    expect($data->gid)->toBe('1100')
        ->and($data->name)->toBeNull()
        ->and($data->owner)->toBeNull()
        ->and($data->workspace)->toBeNull();
});

test('UserTaskListData casts nested owner and workspace to CompactResource', function () {
    $data = UserTaskListData::from([
        'gid' => '1100',
        'owner' => ['gid' => '111', 'name' => 'Jane', 'resource_type' => 'user'],
        'workspace' => ['gid' => 'ws1', 'name' => 'Acme', 'resource_type' => 'workspace'],
    ]);

    expect($data->owner)->toBeInstanceOf(CompactResource::class)
        ->and($data->owner->gid)->toBe('111')
        ->and($data->workspace)->toBeInstanceOf(CompactResource::class)
        ->and($data->workspace->name)->toBe('Acme');
});
