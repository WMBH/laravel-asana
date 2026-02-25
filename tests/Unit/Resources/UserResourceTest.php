<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\UserData;
use WMBH\Asana\Resources\UserResource;

function createUserResource(MockClient $mockClient): UserResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new UserResource($connector);
}

test('get returns UserData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '111',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'resource_type' => 'user',
        ]], 200),
    ]);

    $resource = createUserResource($mockClient);
    $result = $resource->get('111');

    expect($result)->toBeInstanceOf(UserData::class)
        ->and($result->gid)->toBe('111')
        ->and($result->name)->toBe('John Doe')
        ->and($result->email)->toBe('john@example.com');
});

test('me returns UserData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '222',
            'name' => 'Current User',
            'email' => 'me@example.com',
            'resource_type' => 'user',
        ]], 200),
    ]);

    $resource = createUserResource($mockClient);
    $result = $resource->me();

    expect($result)->toBeInstanceOf(UserData::class)
        ->and($result->gid)->toBe('222')
        ->and($result->name)->toBe('Current User');
});

test('list returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'User 1', 'resource_type' => 'user'],
                ['gid' => '2', 'name' => 'User 2', 'resource_type' => 'user'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createUserResource($mockClient);
    $result = $resource->list();

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(UserData::class);
});

test('getForWorkspace returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Workspace User', 'resource_type' => 'user'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createUserResource($mockClient);
    $result = $resource->getForWorkspace('ws1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1)
        ->and($result->data[0]->name)->toBe('Workspace User');
});

test('getForTeam returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Team Member', 'resource_type' => 'user'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createUserResource($mockClient);
    $result = $resource->getForTeam('team1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1)
        ->and($result->data[0]->name)->toBe('Team Member');
});
