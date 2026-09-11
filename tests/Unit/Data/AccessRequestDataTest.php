<?php

use WMBH\Asana\Data\AccessRequestData;
use WMBH\Asana\Data\Shared\CompactResource;

test('AccessRequestData can be created from array', function () {
    $data = AccessRequestData::from([
        'gid' => '1200',
        'resource_type' => 'access_request',
        'message' => 'Please add me',
        'approval_status' => 'pending',
    ]);

    expect($data->gid)->toBe('1200')
        ->and($data->message)->toBe('Please add me')
        ->and($data->approval_status)->toBe('pending');
});

test('AccessRequestData handles null optional fields', function () {
    $data = AccessRequestData::from(['gid' => '1200']);

    expect($data->gid)->toBe('1200')
        ->and($data->resource_type)->toBeNull()
        ->and($data->message)->toBeNull()
        ->and($data->approval_status)->toBeNull()
        ->and($data->requester)->toBeNull()
        ->and($data->target)->toBeNull();
});

test('AccessRequestData casts nested requester and target to CompactResource', function () {
    $data = AccessRequestData::from([
        'gid' => '1200',
        'requester' => ['gid' => '111', 'name' => 'Jane', 'resource_type' => 'user'],
        'target' => ['gid' => '900', 'resource_type' => 'project'],
    ]);

    expect($data->requester)->toBeInstanceOf(CompactResource::class)
        ->and($data->requester->name)->toBe('Jane')
        ->and($data->target)->toBeInstanceOf(CompactResource::class)
        ->and($data->target->gid)->toBe('900')
        ->and($data->target->name)->toBeNull();
});
