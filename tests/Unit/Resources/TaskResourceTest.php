<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\TaskData;
use WMBH\Asana\Query\TaskQueryBuilder;
use WMBH\Asana\Resources\TaskResource;

function createTaskResource(MockClient $mockClient): TaskResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new TaskResource($connector);
}

test('get returns TaskData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '123',
            'name' => 'Test Task',
            'resource_type' => 'task',
            'completed' => false,
        ]], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->get('123');

    expect($result)->toBeInstanceOf(TaskData::class)
        ->and($result->gid)->toBe('123')
        ->and($result->name)->toBe('Test Task')
        ->and($result->completed)->toBeFalse();
});

test('getForProject returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Task 1', 'resource_type' => 'task'],
                ['gid' => '2', 'name' => 'Task 2', 'resource_type' => 'task'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->getForProject('proj1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(TaskData::class)
        ->and($result->data[0]->gid)->toBe('1')
        ->and($result->hasNextPage())->toBeFalse();
});

test('getForProject handles pagination', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Task 1', 'resource_type' => 'task'],
            ],
            'next_page' => ['offset' => 'abc123', 'uri' => '/tasks?offset=abc123'],
        ], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->getForProject('proj1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('abc123');
});

test('getForSection returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'name' => 'Task 1', 'resource_type' => 'task']],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->getForSection('section1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1);
});

test('create returns TaskData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '456',
            'name' => 'New Task',
            'resource_type' => 'task',
        ]], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->create(['name' => 'New Task', 'workspace' => 'ws1']);

    expect($result)->toBeInstanceOf(TaskData::class)
        ->and($result->gid)->toBe('456')
        ->and($result->name)->toBe('New Task');
});

test('update returns TaskData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '123',
            'name' => 'Updated Task',
            'resource_type' => 'task',
        ]], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->update('123', ['name' => 'Updated Task']);

    expect($result)->toBeInstanceOf(TaskData::class)
        ->and($result->name)->toBe('Updated Task');
});

test('delete returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->delete('123');

    expect($result)->toBeTrue();
});

test('addTag sends request without error', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $resource->addTag('task1', 'tag1');

    $mockClient->assertSentCount(1);
});

test('search with empty params returns TaskQueryBuilder', function () {
    $mockClient = new MockClient([]);

    $resource = createTaskResource($mockClient);
    $result = $resource->search('ws1');

    expect($result)->toBeInstanceOf(TaskQueryBuilder::class);
});

test('search with params returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'name' => 'Found', 'resource_type' => 'task']],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->search('ws1', ['assignee.any' => 'me']);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1);
});

test('getSubtasks returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '10', 'name' => 'Subtask 1', 'resource_type' => 'task']],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->getSubtasks('parent1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1);
});

test('setParent returns TaskData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '123',
            'name' => 'Child Task',
            'resource_type' => 'task',
        ]], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->setParent('123', 'parent1');

    expect($result)->toBeInstanceOf(TaskData::class)
        ->and($result->gid)->toBe('123');
});
