<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\WorkspaceData;
use WMBH\Asana\Data\WorkspaceMembershipData;
use WMBH\Asana\Resources\WorkspaceResource;

function createWorkspaceResource(MockClient $mockClient): WorkspaceResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new WorkspaceResource($connector);
}

test('get returns WorkspaceData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '10',
            'name' => 'My Workspace',
            'resource_type' => 'workspace',
            'is_organization' => true,
        ]], 200),
    ]);

    $resource = createWorkspaceResource($mockClient);
    $result = $resource->get('10');

    expect($result)->toBeInstanceOf(WorkspaceData::class)
        ->and($result->gid)->toBe('10')
        ->and($result->name)->toBe('My Workspace')
        ->and($result->is_organization)->toBeTrue();
});

test('list returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Workspace 1', 'resource_type' => 'workspace'],
                ['gid' => '2', 'name' => 'Workspace 2', 'resource_type' => 'workspace'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createWorkspaceResource($mockClient);
    $result = $resource->list();

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(WorkspaceData::class);
});

test('update returns WorkspaceData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '10',
            'name' => 'Renamed Workspace',
            'resource_type' => 'workspace',
        ]], 200),
    ]);

    $resource = createWorkspaceResource($mockClient);
    $result = $resource->update('10', ['name' => 'Renamed Workspace']);

    expect($result)->toBeInstanceOf(WorkspaceData::class)
        ->and($result->name)->toBe('Renamed Workspace');
});

test('addUser sends request without error', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createWorkspaceResource($mockClient);
    $resource->addUser('ws1', 'user1');

    $mockClient->assertSentCount(1);
});

test('removeUser sends request without error', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createWorkspaceResource($mockClient);
    $resource->removeUser('ws1', 'user1');

    $mockClient->assertSentCount(1);
});

test('getMemberships returns PaginatedResponse of WorkspaceMembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'resource_type' => 'workspace_membership', 'is_admin' => true],
                ['gid' => '2', 'resource_type' => 'workspace_membership', 'is_admin' => false],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createWorkspaceResource($mockClient);
    $result = $resource->getMemberships('ws1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(WorkspaceMembershipData::class)
        ->and($result->data[0]->is_admin)->toBeTrue()
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/workspaces/ws1/workspace_memberships'
        && $request->query()->all() === []);
});

test('getMemberships sends user filter and pagination as query params', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => null], 200),
    ]);

    $resource = createWorkspaceResource($mockClient);
    $resource->getMemberships('ws1', 'user1', ['is_admin'], 'abc', 25);

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === [
        'user' => 'user1',
        'opt_fields' => 'is_admin',
        'offset' => 'abc',
        'limit' => 25,
    ]);
});

test('getMemberships handles pagination', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'resource_type' => 'workspace_membership']],
            'next_page' => ['offset' => 'tok', 'uri' => '/workspaces/ws1/workspace_memberships?offset=tok'],
        ], 200),
    ]);

    $resource = createWorkspaceResource($mockClient);
    $result = $resource->getMemberships('ws1');

    expect($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');
});

test('getMembership returns WorkspaceMembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '600',
            'resource_type' => 'workspace_membership',
            'is_guest' => true,
            'user' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        ]], 200),
    ]);

    $resource = createWorkspaceResource($mockClient);
    $result = $resource->getMembership('600', ['is_guest']);

    expect($result)->toBeInstanceOf(WorkspaceMembershipData::class)
        ->and($result->gid)->toBe('600')
        ->and($result->is_guest)->toBeTrue()
        ->and($result->user->name)->toBe('Jane');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/workspace_memberships/600'
        && $request->query()->all() === ['opt_fields' => 'is_guest']);
});

test('typeahead returns PaginatedResponse of CompactResource', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Marketing Launch', 'resource_type' => 'project'],
                ['gid' => '2', 'name' => 'Marketing Site', 'resource_type' => 'project'],
            ],
        ], 200),
    ]);

    $resource = createWorkspaceResource($mockClient);
    $result = $resource->typeahead('ws1', 'project', 'Marketing', 5, ['name']);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(CompactResource::class)
        ->and($result->data[0]->name)->toBe('Marketing Launch')
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/workspaces/ws1/typeahead'
        && $request->query()->all() === [
            'resource_type' => 'project',
            'query' => 'Marketing',
            'count' => 5,
            'opt_fields' => 'name',
        ]);
});

test('typeahead sends only resource_type when query and count are omitted', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createWorkspaceResource($mockClient);
    $resource->typeahead('ws1', 'user');

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === ['resource_type' => 'user']);
});
