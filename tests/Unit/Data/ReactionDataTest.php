<?php

use WMBH\Asana\Data\ReactionData;
use WMBH\Asana\Data\Shared\CompactResource;

test('ReactionData can be created from array', function () {
    $data = ReactionData::from([
        'gid' => '1300',
        'emoji' => '👍',
    ]);

    expect($data->gid)->toBe('1300')
        ->and($data->emoji)->toBe('👍');
});

test('ReactionData handles null optional fields', function () {
    $data = ReactionData::from(['gid' => '1300']);

    expect($data->gid)->toBe('1300')
        ->and($data->emoji)->toBeNull()
        ->and($data->user)->toBeNull();
});

test('ReactionData casts nested user to CompactResource', function () {
    $data = ReactionData::from([
        'gid' => '1300',
        'user' => ['gid' => '111', 'name' => 'Jane', 'resource_type' => 'user'],
    ]);

    expect($data->user)->toBeInstanceOf(CompactResource::class)
        ->and($data->user->gid)->toBe('111')
        ->and($data->user->name)->toBe('Jane');
});
