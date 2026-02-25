<?php

use WMBH\Asana\Data\CustomFieldData;

test('CustomFieldData can be created from array', function () {
    $data = CustomFieldData::from([
        'gid' => '900',
        'resource_type' => 'custom_field',
        'name' => 'Priority',
        'resource_subtype' => 'enum',
    ]);

    expect($data->gid)->toBe('900')
        ->and($data->name)->toBe('Priority')
        ->and($data->resource_subtype)->toBe('enum');
});

test('CustomFieldData handles null optional fields', function () {
    $data = CustomFieldData::from(['gid' => '900']);

    expect($data->gid)->toBe('900')
        ->and($data->name)->toBeNull()
        ->and($data->type)->toBeNull()
        ->and($data->description)->toBeNull()
        ->and($data->enabled)->toBeNull()
        ->and($data->enum_options)->toBeNull()
        ->and($data->precision)->toBeNull()
        ->and($data->format)->toBeNull()
        ->and($data->currency_code)->toBeNull();
});

test('CustomFieldData handles number field', function () {
    $data = CustomFieldData::from([
        'gid' => '901',
        'resource_type' => 'custom_field',
        'name' => 'Story Points',
        'resource_subtype' => 'number',
        'precision' => 0,
        'format' => 'none',
    ]);

    expect($data->precision)->toBe(0)
        ->and($data->format)->toBe('none');
});

test('CustomFieldData handles enum field with options', function () {
    $data = CustomFieldData::from([
        'gid' => '902',
        'resource_type' => 'custom_field',
        'name' => 'Status',
        'resource_subtype' => 'enum',
        'enum_options' => [
            ['gid' => '1', 'name' => 'Low', 'color' => 'green'],
            ['gid' => '2', 'name' => 'High', 'color' => 'red'],
        ],
    ]);

    expect($data->enum_options)->toBeArray()->toHaveCount(2);
});
