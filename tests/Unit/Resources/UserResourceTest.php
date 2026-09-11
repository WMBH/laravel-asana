<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\TeamMembershipData;
use WMBH\Asana\Data\UserData;
use WMBH\Asana\Data\WorkspaceMembershipData;
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

test('update returns UserData and sends PUT body', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '111',
            'name' => 'Johnathan Doe',
            'resource_type' => 'user',
        ]], 200),
    ]);

    $resource = createUserResource($mockClient);
    $result = $resource->update('111', ['name' => 'Johnathan Doe']);

    expect($result)->toBeInstanceOf(UserData::class)
        ->and($result->name)->toBe('Johnathan Doe');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/users/111'
        && $request->body()->all() === ['data' => ['name' => 'Johnathan Doe']]
        && $request->query()->all() === []);
});

test('update sends workspace and opt_fields as query params', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => ['gid' => '111', 'resource_type' => 'user']], 200),
    ]);

    $resource = createUserResource($mockClient);
    $resource->update('111', ['custom_fields' => ['123' => 'x']], 'ws1', ['name', 'custom_fields']);

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === [
        'workspace' => 'ws1',
        'opt_fields' => 'name,custom_fields',
    ]);
});

test('getFavorites returns PaginatedResponse of CompactResource', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Roadmap', 'resource_type' => 'project'],
                ['gid' => '2', 'name' => 'Backlog', 'resource_type' => 'project'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createUserResource($mockClient);
    $result = $resource->getFavorites('me', 'project', 'ws1', 'abc', 20, ['name']);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(CompactResource::class)
        ->and($result->data[0]->name)->toBe('Roadmap')
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/users/me/favorites'
        && $request->query()->all() === [
            'resource_type' => 'project',
            'workspace' => 'ws1',
            'offset' => 'abc',
            'limit' => 20,
            'opt_fields' => 'name',
        ]);
});

test('getInWorkspace returns UserData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '111',
            'name' => 'John Doe',
            'resource_type' => 'user',
        ]], 200),
    ]);

    $resource = createUserResource($mockClient);
    $result = $resource->getInWorkspace('ws1', '111', ['name']);

    expect($result)->toBeInstanceOf(UserData::class)
        ->and($result->gid)->toBe('111');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/workspaces/ws1/users/111'
        && $request->query()->all() === ['opt_fields' => 'name']);
});

test('updateInWorkspace returns UserData and sends PUT body', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '111',
            'name' => 'John Doe',
            'resource_type' => 'user',
        ]], 200),
    ]);

    $resource = createUserResource($mockClient);
    $result = $resource->updateInWorkspace('ws1', '111', ['custom_fields' => ['123' => 'x']]);

    expect($result)->toBeInstanceOf(UserData::class)
        ->and($result->gid)->toBe('111');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/workspaces/ws1/users/111'
        && $request->body()->all() === ['data' => ['custom_fields' => ['123' => 'x']]]);
});

test('getTeamMemberships returns PaginatedResponse of TeamMembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'resource_type' => 'team_membership', 'is_admin' => false]],
            'next_page' => ['offset' => 'tok', 'uri' => '/users/111/team_memberships?offset=tok'],
        ], 200),
    ]);

    $resource = createUserResource($mockClient);
    $result = $resource->getTeamMemberships('111', 'ws1', ['is_admin'], null, 10);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data[0])->toBeInstanceOf(TeamMembershipData::class)
        ->and($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/users/111/team_memberships'
        && $request->query()->all() === [
            'workspace' => 'ws1',
            'opt_fields' => 'is_admin',
            'limit' => 10,
        ]);
});

test('getWorkspaceMemberships returns PaginatedResponse of WorkspaceMembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'resource_type' => 'workspace_membership', 'is_guest' => true]],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createUserResource($mockClient);
    $result = $resource->getWorkspaceMemberships('111', ['is_guest'], 'abc', 5);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data[0])->toBeInstanceOf(WorkspaceMembershipData::class)
        ->and($result->data[0]->is_guest)->toBeTrue()
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/users/111/workspace_memberships'
        && $request->query()->all() === [
            'opt_fields' => 'is_guest',
            'offset' => 'abc',
            'limit' => 5,
        ]);
});
