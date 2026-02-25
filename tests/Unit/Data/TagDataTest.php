<?php

use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\TagData;

test('TagData can be created from array', function () {
    $data = TagData::from([
        'gid' => '300',
        'resource_type' => 'tag',
        'name' => 'Priority',
        'color' => 'red',
    ]);

    expect($data->gid)->toBe('300')
        ->and($data->name)->toBe('Priority')
        ->and($data->color)->toBe('red');
});

test('TagData handles null optional fields', function () {
    $data = TagData::from(['gid' => '300']);

    expect($data->gid)->toBe('300')
        ->and($data->name)->toBeNull()
        ->and($data->color)->toBeNull()
        ->and($data->notes)->toBeNull()
        ->and($data->followers)->toBeNull()
        ->and($data->workspace)->toBeNull();
});

test('TagData casts nested workspace to CompactResource', function () {
    $data = TagData::from([
        'gid' => '300',
        'workspace' => ['gid' => '1', 'name' => 'WS', 'resource_type' => 'workspace'],
    ]);

    expect($data->workspace)->toBeInstanceOf(CompactResource::class)
        ->and($data->workspace->gid)->toBe('1');
});
