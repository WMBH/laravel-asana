<?php

use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\TeamData;

test('TeamData can be created from array', function () {
    $data = TeamData::from([
        'gid' => '50',
        'resource_type' => 'team',
        'name' => 'Engineering',
        'description' => 'The eng team',
    ]);

    expect($data->gid)->toBe('50')
        ->and($data->name)->toBe('Engineering')
        ->and($data->description)->toBe('The eng team');
});

test('TeamData handles null optional fields', function () {
    $data = TeamData::from(['gid' => '50']);

    expect($data->gid)->toBe('50')
        ->and($data->name)->toBeNull()
        ->and($data->description)->toBeNull()
        ->and($data->html_description)->toBeNull()
        ->and($data->organization)->toBeNull()
        ->and($data->permalink_url)->toBeNull();
});

test('TeamData casts nested organization to CompactResource', function () {
    $data = TeamData::from([
        'gid' => '50',
        'organization' => ['gid' => '1', 'name' => 'Acme Corp', 'resource_type' => 'workspace'],
    ]);

    expect($data->organization)->toBeInstanceOf(CompactResource::class)
        ->and($data->organization->gid)->toBe('1')
        ->and($data->organization->name)->toBe('Acme Corp');
});
