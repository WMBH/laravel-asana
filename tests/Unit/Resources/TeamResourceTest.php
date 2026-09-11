<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\TeamData;
use WMBH\Asana\Data\TeamMembershipData;
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

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/workspaces/ws1/teams');
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

test('update returns TeamData and sends PUT body with opt_fields', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '50',
            'name' => 'Platform',
            'resource_type' => 'team',
        ]], 200),
    ]);

    $resource = createTeamResource($mockClient);
    $result = $resource->update('50', ['name' => 'Platform'], ['name']);

    expect($result)->toBeInstanceOf(TeamData::class)
        ->and($result->name)->toBe('Platform');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/teams/50'
        && $request->body()->all() === ['data' => ['name' => 'Platform']]
        && $request->query()->all() === ['opt_fields' => 'name']);
});

test('getMemberships returns PaginatedResponse of TeamMembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'resource_type' => 'team_membership', 'is_admin' => true],
                ['gid' => '2', 'resource_type' => 'team_membership', 'is_admin' => false],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createTeamResource($mockClient);
    $result = $resource->getMemberships('team1', 'user1', 'ws1', ['is_admin'], 'abc', 10);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(TeamMembershipData::class)
        ->and($result->data[0]->is_admin)->toBeTrue()
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/team_memberships'
        && $request->query()->all() === [
            'team' => 'team1',
            'user' => 'user1',
            'workspace' => 'ws1',
            'opt_fields' => 'is_admin',
            'offset' => 'abc',
            'limit' => 10,
        ]);
});

test('getMemberships omits null filters', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => null], 200),
    ]);

    $resource = createTeamResource($mockClient);
    $resource->getMemberships();

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === []);
});

test('getMembershipsForTeam returns PaginatedResponse and handles pagination', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'resource_type' => 'team_membership']],
            'next_page' => ['offset' => 'tok', 'uri' => '/teams/team1/team_memberships?offset=tok'],
        ], 200),
    ]);

    $resource = createTeamResource($mockClient);
    $result = $resource->getMembershipsForTeam('team1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data[0])->toBeInstanceOf(TeamMembershipData::class)
        ->and($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/teams/team1/team_memberships');
});

test('getMembership returns TeamMembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '800',
            'resource_type' => 'team_membership',
            'is_guest' => true,
            'user' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        ]], 200),
    ]);

    $resource = createTeamResource($mockClient);
    $result = $resource->getMembership('800', ['is_guest']);

    expect($result)->toBeInstanceOf(TeamMembershipData::class)
        ->and($result->gid)->toBe('800')
        ->and($result->is_guest)->toBeTrue()
        ->and($result->user->name)->toBe('Jane');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/team_memberships/800'
        && $request->query()->all() === ['opt_fields' => 'is_guest']);
});
