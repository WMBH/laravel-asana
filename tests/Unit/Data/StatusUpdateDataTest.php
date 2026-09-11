<?php

use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\StatusUpdateData;

test('StatusUpdateData can be created from array', function () {
    $data = StatusUpdateData::from([
        'gid' => '700',
        'resource_type' => 'status_update',
        'resource_subtype' => 'project_status_update',
        'title' => 'On track',
        'text' => 'All good',
        'status_type' => 'on_track',
    ]);

    expect($data->gid)->toBe('700')
        ->and($data->resource_subtype)->toBe('project_status_update')
        ->and($data->title)->toBe('On track')
        ->and($data->text)->toBe('All good')
        ->and($data->status_type)->toBe('on_track');
});

test('StatusUpdateData handles null optional fields', function () {
    $data = StatusUpdateData::from(['gid' => '700']);

    expect($data->gid)->toBe('700')
        ->and($data->title)->toBeNull()
        ->and($data->text)->toBeNull()
        ->and($data->html_text)->toBeNull()
        ->and($data->status_type)->toBeNull()
        ->and($data->author)->toBeNull()
        ->and($data->created_by)->toBeNull()
        ->and($data->parent)->toBeNull()
        ->and($data->hearted)->toBeNull()
        ->and($data->hearts)->toBeNull()
        ->and($data->num_likes)->toBeNull()
        ->and($data->reaction_summary)->toBeNull();
});

test('StatusUpdateData casts nested author to CompactResource', function () {
    $data = StatusUpdateData::from([
        'gid' => '700',
        'author' => ['gid' => '111', 'name' => 'Jane', 'resource_type' => 'user'],
    ]);

    expect($data->author)->toBeInstanceOf(CompactResource::class)
        ->and($data->author->gid)->toBe('111')
        ->and($data->author->name)->toBe('Jane');
});

test('StatusUpdateData casts nested parent to CompactResource', function () {
    $data = StatusUpdateData::from([
        'gid' => '700',
        'parent' => ['gid' => '900', 'name' => 'Project X', 'resource_type' => 'project'],
    ]);

    expect($data->parent)->toBeInstanceOf(CompactResource::class)
        ->and($data->parent->gid)->toBe('900');
});
