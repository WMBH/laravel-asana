<?php

use WMBH\Asana\Data\SectionData;
use WMBH\Asana\Data\Shared\CompactResource;

test('SectionData can be created from array', function () {
    $data = SectionData::from([
        'gid' => '100',
        'resource_type' => 'section',
        'name' => 'To Do',
    ]);

    expect($data->gid)->toBe('100')
        ->and($data->name)->toBe('To Do')
        ->and($data->resource_type)->toBe('section');
});

test('SectionData handles null optional fields', function () {
    $data = SectionData::from(['gid' => '100']);

    expect($data->gid)->toBe('100')
        ->and($data->name)->toBeNull()
        ->and($data->created_at)->toBeNull()
        ->and($data->project)->toBeNull();
});

test('SectionData casts nested project to CompactResource', function () {
    $data = SectionData::from([
        'gid' => '100',
        'project' => ['gid' => '200', 'name' => 'My Project', 'resource_type' => 'project'],
    ]);

    expect($data->project)->toBeInstanceOf(CompactResource::class)
        ->and($data->project->gid)->toBe('200')
        ->and($data->project->name)->toBe('My Project');
});
