<?php

use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\TeamMembershipData;

test('TeamMembershipData can be created from array', function () {
    $data = TeamMembershipData::from([
        'gid' => '800',
        'resource_type' => 'team_membership',
        'is_guest' => false,
        'is_limited_access' => false,
        'is_admin' => true,
    ]);

    expect($data->gid)->toBe('800')
        ->and($data->resource_type)->toBe('team_membership')
        ->and($data->is_guest)->toBeFalse()
        ->and($data->is_limited_access)->toBeFalse()
        ->and($data->is_admin)->toBeTrue();
});

test('TeamMembershipData handles null optional fields', function () {
    $data = TeamMembershipData::from(['gid' => '800']);

    expect($data->gid)->toBe('800')
        ->and($data->resource_type)->toBeNull()
        ->and($data->user)->toBeNull()
        ->and($data->team)->toBeNull()
        ->and($data->is_guest)->toBeNull()
        ->and($data->is_limited_access)->toBeNull()
        ->and($data->is_admin)->toBeNull();
});

test('TeamMembershipData casts user and team to CompactResource', function () {
    $data = TeamMembershipData::from([
        'gid' => '800',
        'user' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        'team' => ['gid' => '50', 'name' => 'Engineering', 'resource_type' => 'team'],
    ]);

    expect($data->user)->toBeInstanceOf(CompactResource::class)
        ->and($data->user->name)->toBe('Jane')
        ->and($data->team)->toBeInstanceOf(CompactResource::class)
        ->and($data->team->gid)->toBe('50');
});
