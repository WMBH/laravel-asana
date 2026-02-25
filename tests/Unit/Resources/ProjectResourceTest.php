<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\ProjectData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Resources\ProjectResource;

function createProjectResource(MockClient $mockClient): ProjectResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new ProjectResource($connector);
}

test('get returns ProjectData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '789',
            'name' => 'Test Project',
            'resource_type' => 'project',
            'archived' => false,
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->get('789');

    expect($result)->toBeInstanceOf(ProjectData::class)
        ->and($result->gid)->toBe('789')
        ->and($result->name)->toBe('Test Project')
        ->and($result->archived)->toBeFalse();
});

test('list returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Project 1', 'resource_type' => 'project'],
                ['gid' => '2', 'name' => 'Project 2', 'resource_type' => 'project'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->list('ws1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(ProjectData::class)
        ->and($result->data[0]->name)->toBe('Project 1');
});

test('create returns ProjectData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '999',
            'name' => 'New Project',
            'resource_type' => 'project',
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->create(['name' => 'New Project', 'workspace' => 'ws1']);

    expect($result)->toBeInstanceOf(ProjectData::class)
        ->and($result->gid)->toBe('999')
        ->and($result->name)->toBe('New Project');
});

test('update returns ProjectData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '789',
            'name' => 'Updated Project',
            'resource_type' => 'project',
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->update('789', ['name' => 'Updated Project']);

    expect($result)->toBeInstanceOf(ProjectData::class)
        ->and($result->name)->toBe('Updated Project');
});

test('delete returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->delete('789');

    expect($result)->toBeTrue();
});

test('duplicate returns array data', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1000',
            'new_project' => ['gid' => '1001', 'name' => 'Copy of Project'],
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->duplicate('789', ['name' => 'Copy of Project']);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('gid');
});

test('getTaskCounts returns array data', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'num_tasks' => 10,
            'num_incomplete_tasks' => 4,
            'num_completed_tasks' => 6,
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->getTaskCounts('789');

    expect($result)->toBeArray()
        ->and($result['num_tasks'])->toBe(10)
        ->and($result['num_incomplete_tasks'])->toBe(4);
});

test('getForTeam returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Team Project', 'resource_type' => 'project'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->getForTeam('team1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1)
        ->and($result->data[0]->name)->toBe('Team Project');
});
