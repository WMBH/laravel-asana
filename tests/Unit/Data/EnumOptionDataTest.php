<?php

use WMBH\Asana\Data\EnumOptionData;

test('EnumOptionData can be created from array', function () {
    $data = EnumOptionData::from([
        'gid' => '11',
        'resource_type' => 'enum_option',
        'name' => 'Urgent',
        'enabled' => true,
        'color' => 'red',
    ]);

    expect($data->gid)->toBe('11')
        ->and($data->resource_type)->toBe('enum_option')
        ->and($data->name)->toBe('Urgent')
        ->and($data->enabled)->toBeTrue()
        ->and($data->color)->toBe('red');
});

test('EnumOptionData handles null optional fields', function () {
    $data = EnumOptionData::from(['gid' => '11']);

    expect($data->gid)->toBe('11')
        ->and($data->resource_type)->toBeNull()
        ->and($data->name)->toBeNull()
        ->and($data->enabled)->toBeNull()
        ->and($data->color)->toBeNull();
});
