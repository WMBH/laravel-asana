<?php

use WMBH\Asana\Data\GoalData;
use WMBH\Asana\Data\Shared\CompactResource;

test('GoalData can be created from array', function () {
    $data = GoalData::from([
        'gid' => '1100',
        'resource_type' => 'goal',
        'name' => 'Ship v2.0',
        'status' => 'green',
    ]);

    expect($data->gid)->toBe('1100')
        ->and($data->name)->toBe('Ship v2.0')
        ->and($data->status)->toBe('green');
});

test('GoalData handles null optional fields', function () {
    $data = GoalData::from(['gid' => '1100']);

    expect($data->gid)->toBe('1100')
        ->and($data->name)->toBeNull()
        ->and($data->owner)->toBeNull()
        ->and($data->due_on)->toBeNull()
        ->and($data->status)->toBeNull()
        ->and($data->metric)->toBeNull()
        ->and($data->team)->toBeNull()
        ->and($data->workspace)->toBeNull()
        ->and($data->followers)->toBeNull();
});

test('GoalData casts nested owner to CompactResource', function () {
    $data = GoalData::from([
        'gid' => '1100',
        'owner' => ['gid' => '111', 'name' => 'John', 'resource_type' => 'user'],
    ]);

    expect($data->owner)->toBeInstanceOf(CompactResource::class)
        ->and($data->owner->gid)->toBe('111');
});

test('GoalData casts nested team to CompactResource', function () {
    $data = GoalData::from([
        'gid' => '1100',
        'team' => ['gid' => '50', 'name' => 'Eng', 'resource_type' => 'team'],
    ]);

    expect($data->team)->toBeInstanceOf(CompactResource::class)
        ->and($data->team->gid)->toBe('50');
});

test('GoalData handles full payload', function () {
    $data = GoalData::from([
        'gid' => '1100',
        'resource_type' => 'goal',
        'name' => 'Ship v2.0',
        'due_on' => '2025-06-30',
        'start_on' => '2025-01-01',
        'status' => 'green',
        'notes' => 'Release notes',
        'is_workspace_level' => true,
        'liked' => false,
        'num_likes' => 5,
        'metric' => ['current_number_value' => 75],
    ]);

    expect($data->due_on)->toBe('2025-06-30')
        ->and($data->is_workspace_level)->toBeTrue()
        ->and($data->num_likes)->toBe(5)
        ->and($data->metric)->toBeArray();
});
