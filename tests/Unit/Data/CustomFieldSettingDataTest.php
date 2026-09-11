<?php

use WMBH\Asana\Data\CustomFieldSettingData;
use WMBH\Asana\Data\Shared\CompactResource;

test('CustomFieldSettingData can be created from array', function () {
    $data = CustomFieldSettingData::from([
        'gid' => '55',
        'resource_type' => 'custom_field_setting',
        'is_important' => true,
        'custom_field' => ['gid' => '900', 'name' => 'Priority', 'resource_subtype' => 'enum'],
    ]);

    expect($data->gid)->toBe('55')
        ->and($data->resource_type)->toBe('custom_field_setting')
        ->and($data->is_important)->toBeTrue()
        ->and($data->custom_field)->toBeArray()
        ->and($data->custom_field['name'])->toBe('Priority');
});

test('CustomFieldSettingData handles null optional fields', function () {
    $data = CustomFieldSettingData::from(['gid' => '55']);

    expect($data->gid)->toBe('55')
        ->and($data->resource_type)->toBeNull()
        ->and($data->project)->toBeNull()
        ->and($data->parent)->toBeNull()
        ->and($data->is_important)->toBeNull()
        ->and($data->custom_field)->toBeNull();
});

test('CustomFieldSettingData casts nested project and parent to CompactResource', function () {
    $data = CustomFieldSettingData::from([
        'gid' => '55',
        'project' => ['gid' => '789', 'resource_type' => 'project', 'name' => 'Sprint'],
        'parent' => ['gid' => '789', 'resource_type' => 'project', 'name' => 'Sprint'],
    ]);

    expect($data->project)->toBeInstanceOf(CompactResource::class)
        ->and($data->project->gid)->toBe('789')
        ->and($data->parent)->toBeInstanceOf(CompactResource::class)
        ->and($data->parent->name)->toBe('Sprint');
});
