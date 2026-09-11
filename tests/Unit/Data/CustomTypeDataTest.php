<?php

use WMBH\Asana\Data\CustomTypeData;

test('CustomTypeData can be created from array', function () {
    $data = CustomTypeData::from([
        'gid' => '1000',
        'resource_type' => 'custom_type',
        'name' => 'Bug',
        'asana_created_type_identifier' => null,
        'status_options' => [
            ['gid' => '1', 'name' => 'Open', 'enabled' => true],
        ],
    ]);

    expect($data->gid)->toBe('1000')
        ->and($data->name)->toBe('Bug')
        ->and($data->asana_created_type_identifier)->toBeNull()
        ->and($data->status_options)->toHaveCount(1)
        ->and($data->status_options[0]['name'])->toBe('Open');
});

test('CustomTypeData handles null optional fields', function () {
    $data = CustomTypeData::from(['gid' => '1000']);

    expect($data->gid)->toBe('1000')
        ->and($data->resource_type)->toBeNull()
        ->and($data->name)->toBeNull()
        ->and($data->asana_created_type_identifier)->toBeNull()
        ->and($data->status_options)->toBeNull();
});
