<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\TeamData;
use WMBH\Asana\Resources\TeamResource;

function createTeamResource(MockClient $mockClient): TeamResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new TeamResource($connector);
}

test('get returns TeamData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '50',
            'name' => 'Engineering',
            'resource_type' => 'team',
            'description' => 'The eng team',
        ]], 200),
    ]);

    $resource = createTeamResource($mockClient);
    $result = $resource->get('50');

    expect($result)->toBeInstanceOf(TeamData::class)
        ->and($result->gid)->toBe('50')
        ->and($result->name)->toBe('Engineering')
        ->and($result->description)->toBe('The eng team');
});

test('getForWorkspace returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Team A', 'resource_type' => 'team'],
                ['gid' => '2', 'name' => 'Team B', 'resource_type' => 'team'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createTeamResource($mockClient);
    $result = $resource->getForWorkspace('ws1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(TeamData::class);
});

test('getForUser returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'name' => 'User Team', 'resource_type' => 'team']],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createTeamResource($mockClient);
    $result = $resource->getForUser('user1', 'org1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1);
});

test('create returns TeamData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '60',
            'name' => 'New Team',
            'resource_type' => 'team',
        ]], 200),
    ]);

    $resource = createTeamResource($mockClient);
    $result = $resource->create(['name' => 'New Team', 'organization' => 'org1']);

    expect($result)->toBeInstanceOf(TeamData::class)
        ->and($result->gid)->toBe('60')
        ->and($result->name)->toBe('New Team');
});

test('addUser sends request without error', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createTeamResource($mockClient);
    $resource->addUser('team1', 'user1');

    $mockClient->assertSentCount(1);
});

test('removeUser sends request without error', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createTeamResource($mockClient);
    $resource->removeUser('team1', 'user1');

    $mockClient->assertSentCount(1);
});
