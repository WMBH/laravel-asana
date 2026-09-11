<?php

use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\WorkspaceMembershipData;

test('WorkspaceMembershipData can be created from array', function () {
    $data = WorkspaceMembershipData::from([
        'gid' => '600',
        'resource_type' => 'workspace_membership',
        'is_active' => true,
        'is_admin' => false,
        'is_guest' => false,
        'is_view_only' => false,
        'created_at' => '2026-01-01T00:00:00.000Z',
        'vacation_dates' => ['start_on' => '2026-07-01', 'end_on' => '2026-07-14'],
    ]);

    expect($data->gid)->toBe('600')
        ->and($data->resource_type)->toBe('workspace_membership')
        ->and($data->is_active)->toBeTrue()
        ->and($data->is_admin)->toBeFalse()
        ->and($data->is_view_only)->toBeFalse()
        ->and($data->created_at)->toBe('2026-01-01T00:00:00.000Z')
        ->and($data->vacation_dates)->toBe(['start_on' => '2026-07-01', 'end_on' => '2026-07-14']);
});

test('WorkspaceMembershipData handles null optional fields', function () {
    $data = WorkspaceMembershipData::from(['gid' => '600']);

    expect($data->gid)->toBe('600')
        ->and($data->resource_type)->toBeNull()
        ->and($data->user)->toBeNull()
        ->and($data->workspace)->toBeNull()
        ->and($data->user_task_list)->toBeNull()
        ->and($data->is_active)->toBeNull()
        ->and($data->is_admin)->toBeNull()
        ->and($data->is_guest)->toBeNull()
        ->and($data->is_view_only)->toBeNull()
        ->and($data->vacation_dates)->toBeNull()
        ->and($data->created_at)->toBeNull();
});

test('WorkspaceMembershipData casts nested references to CompactResource', function () {
    $data = WorkspaceMembershipData::from([
        'gid' => '600',
        'user' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        'workspace' => ['gid' => '10', 'name' => 'My Workspace', 'resource_type' => 'workspace'],
        'user_task_list' => ['gid' => '30', 'name' => 'My Tasks', 'resource_type' => 'user_task_list'],
    ]);

    expect($data->user)->toBeInstanceOf(CompactResource::class)
        ->and($data->user->name)->toBe('Jane')
        ->and($data->workspace)->toBeInstanceOf(CompactResource::class)
        ->and($data->workspace->gid)->toBe('10')
        ->and($data->user_task_list)->toBeInstanceOf(CompactResource::class)
        ->and($data->user_task_list->name)->toBe('My Tasks');
});
