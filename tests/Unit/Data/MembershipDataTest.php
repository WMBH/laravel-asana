<?php

use WMBH\Asana\Data\MembershipData;
use WMBH\Asana\Data\Shared\CompactResource;

test('MembershipData can be created from array', function () {
    $data = MembershipData::from([
        'gid' => '900',
        'resource_type' => 'project_membership',
        'resource_subtype' => 'project_membership',
        'access_level' => 'editor',
    ]);

    expect($data->gid)->toBe('900')
        ->and($data->resource_type)->toBe('project_membership')
        ->and($data->resource_subtype)->toBe('project_membership')
        ->and($data->access_level)->toBe('editor');
});

test('MembershipData handles null optional fields', function () {
    $data = MembershipData::from(['gid' => '900']);

    expect($data->gid)->toBe('900')
        ->and($data->resource_type)->toBeNull()
        ->and($data->resource_subtype)->toBeNull()
        ->and($data->parent)->toBeNull()
        ->and($data->member)->toBeNull()
        ->and($data->access_level)->toBeNull()
        ->and($data->role)->toBeNull()
        ->and($data->user)->toBeNull()
        ->and($data->goal)->toBeNull()
        ->and($data->workspace)->toBeNull()
        ->and($data->project)->toBeNull()
        ->and($data->write_access)->toBeNull();
});

test('MembershipData casts nested references to CompactResource', function () {
    $data = MembershipData::from([
        'gid' => '900',
        'parent' => ['gid' => '1', 'name' => 'Project A', 'resource_type' => 'project'],
        'member' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        'user' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        'goal' => ['gid' => '3', 'name' => 'Goal', 'resource_type' => 'goal'],
        'workspace' => ['gid' => '4', 'name' => 'WS', 'resource_type' => 'workspace'],
        'project' => ['gid' => '1', 'name' => 'Project A', 'resource_type' => 'project'],
    ]);

    expect($data->parent)->toBeInstanceOf(CompactResource::class)
        ->and($data->parent->name)->toBe('Project A')
        ->and($data->member)->toBeInstanceOf(CompactResource::class)
        ->and($data->member->gid)->toBe('2')
        ->and($data->user)->toBeInstanceOf(CompactResource::class)
        ->and($data->goal)->toBeInstanceOf(CompactResource::class)
        ->and($data->workspace)->toBeInstanceOf(CompactResource::class)
        ->and($data->project)->toBeInstanceOf(CompactResource::class);
});
