<?php

use WMBH\Asana\Data\UserData;

test('UserData can be created from array', function () {
    $data = UserData::from([
        'gid' => '111',
        'resource_type' => 'user',
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    expect($data->gid)->toBe('111')
        ->and($data->name)->toBe('John Doe')
        ->and($data->email)->toBe('john@example.com');
});

test('UserData handles null optional fields', function () {
    $data = UserData::from(['gid' => '111']);

    expect($data->gid)->toBe('111')
        ->and($data->name)->toBeNull()
        ->and($data->email)->toBeNull()
        ->and($data->photo)->toBeNull()
        ->and($data->workspaces)->toBeNull();
});

test('UserData handles full payload with photo and workspaces', function () {
    $data = UserData::from([
        'gid' => '111',
        'resource_type' => 'user',
        'name' => 'Jane',
        'email' => 'jane@example.com',
        'photo' => ['image_128x128' => 'https://example.com/photo.png'],
        'workspaces' => [['gid' => '1', 'name' => 'WS']],
    ]);

    expect($data->photo)->toBeArray()
        ->and($data->workspaces)->toBeArray()->toHaveCount(1);
});
