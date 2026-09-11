<?php

use WMBH\Asana\Data\ProjectMembershipData;
use WMBH\Asana\Data\Shared\CompactResource;

test('ProjectMembershipData can be created from array', function () {
    $data = ProjectMembershipData::from([
        'gid' => '700',
        'resource_type' => 'project_membership',
        'resource_subtype' => 'project_membership',
        'access_level' => 'editor',
        'write_access' => 'full_write',
    ]);

    expect($data->gid)->toBe('700')
        ->and($data->resource_type)->toBe('project_membership')
        ->and($data->access_level)->toBe('editor')
        ->and($data->write_access)->toBe('full_write');
});

test('ProjectMembershipData handles null optional fields', function () {
    $data = ProjectMembershipData::from(['gid' => '700']);

    expect($data->gid)->toBe('700')
        ->and($data->resource_type)->toBeNull()
        ->and($data->resource_subtype)->toBeNull()
        ->and($data->parent)->toBeNull()
        ->and($data->member)->toBeNull()
        ->and($data->access_level)->toBeNull()
        ->and($data->user)->toBeNull()
        ->and($data->project)->toBeNull()
        ->and($data->write_access)->toBeNull();
});

test('ProjectMembershipData casts nested references to CompactResource', function () {
    $data = ProjectMembershipData::from([
        'gid' => '700',
        'parent' => ['gid' => '1', 'name' => 'Project A', 'resource_type' => 'project'],
        'member' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        'user' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        'project' => ['gid' => '1', 'name' => 'Project A', 'resource_type' => 'project'],
    ]);

    expect($data->parent)->toBeInstanceOf(CompactResource::class)
        ->and($data->member)->toBeInstanceOf(CompactResource::class)
        ->and($data->member->name)->toBe('Jane')
        ->and($data->user)->toBeInstanceOf(CompactResource::class)
        ->and($data->project)->toBeInstanceOf(CompactResource::class)
        ->and($data->project->gid)->toBe('1');
});
