<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\WorkspaceData;
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
