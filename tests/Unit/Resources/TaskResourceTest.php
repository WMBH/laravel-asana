<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\JobData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\TaskData;
use WMBH\Asana\Query\TaskQueryBuilder;
use WMBH\Asana\Requests\Tasks\CreateSubtaskRequest;
use WMBH\Asana\Requests\Tasks\DuplicateTaskRequest;
use WMBH\Asana\Requests\Tasks\RemoveDependenciesRequest;
use WMBH\Asana\Requests\Tasks\RemoveDependentsRequest;
use WMBH\Asana\Requests\Tasks\RemoveFollowersRequest;
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

test('list returns PaginatedResponse and forwards params', function () {
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
    $result = $resource->list(['assignee' => 'me', 'workspace' => 'ws1', 'completed_since' => 'now'], ['name'], 'abc', 20);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(TaskData::class)
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/tasks'
        && $request->query()->all() === [
            'assignee' => 'me',
            'workspace' => 'ws1',
            'completed_since' => 'now',
            'opt_fields' => 'name',
            'offset' => 'abc',
            'limit' => 20,
        ]);
});

test('duplicate returns JobData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => 'job1',
            'resource_type' => 'job',
            'resource_subtype' => 'duplicate_task',
            'status' => 'in_progress',
            'new_task' => ['gid' => 't2', 'name' => 'Copy', 'resource_type' => 'task'],
        ]], 201),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->duplicate('t1', ['name' => 'Copy', 'include' => 'notes,assignee']);

    expect($result)->toBeInstanceOf(JobData::class)
        ->and($result->gid)->toBe('job1')
        ->and($result->new_task->gid)->toBe('t2');

    $mockClient->assertSent(fn (Request $request) => $request instanceof DuplicateTaskRequest
        && $request->resolveEndpoint() === '/tasks/t1/duplicate'
        && $request->body()->all() === ['data' => ['name' => 'Copy', 'include' => 'notes,assignee']]);
});

test('getForTag returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'name' => 'Tagged', 'resource_type' => 'task']],
            'next_page' => ['offset' => 'tok', 'uri' => '/tags/tag1/tasks?offset=tok'],
        ], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->getForTag('tag1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data[0])->toBeInstanceOf(TaskData::class)
        ->and($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/tags/tag1/tasks');
});

test('getForUserTaskList returns PaginatedResponse and forwards completed_since', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'name' => 'My task', 'resource_type' => 'task']],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->getForUserTaskList('utl1', ['name'], null, 50, 'now');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1);

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/user_task_lists/utl1/tasks'
        && $request->query()->all() === ['opt_fields' => 'name', 'limit' => 50, 'completed_since' => 'now']);
});

test('createSubtask returns TaskData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => 'sub1',
            'name' => 'Subtask',
            'resource_type' => 'task',
            'parent' => ['gid' => 't1', 'name' => 'Parent', 'resource_type' => 'task'],
        ]], 201),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->createSubtask('t1', ['name' => 'Subtask']);

    expect($result)->toBeInstanceOf(TaskData::class)
        ->and($result->gid)->toBe('sub1')
        ->and($result->parent->gid)->toBe('t1');

    $mockClient->assertSent(fn (Request $request) => $request instanceof CreateSubtaskRequest
        && $request->resolveEndpoint() === '/tasks/t1/subtasks'
        && $request->body()->all() === ['data' => ['name' => 'Subtask']]);
});

test('removeDependencies sends dependency gids', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $resource->removeDependencies('t1', ['d1', 'd2']);

    $mockClient->assertSent(fn (Request $request) => $request instanceof RemoveDependenciesRequest
        && $request->resolveEndpoint() === '/tasks/t1/removeDependencies'
        && $request->body()->all() === ['data' => ['dependencies' => ['d1', 'd2']]]);
});

test('removeDependents sends dependent gids', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $resource->removeDependents('t1', ['d3']);

    $mockClient->assertSent(fn (Request $request) => $request instanceof RemoveDependentsRequest
        && $request->resolveEndpoint() === '/tasks/t1/removeDependents'
        && $request->body()->all() === ['data' => ['dependents' => ['d3']]]);
});

test('removeFollowers sends followers', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => ['gid' => 't1', 'resource_type' => 'task']], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $resource->removeFollowers('t1', ['u1', 'u2']);

    $mockClient->assertSent(fn (Request $request) => $request instanceof RemoveFollowersRequest
        && $request->resolveEndpoint() === '/tasks/t1/removeFollowers'
        && $request->body()->all() === ['data' => ['followers' => ['u1', 'u2']]]);
});

test('getByCustomId returns TaskData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => 't9',
            'name' => 'Custom ID task',
            'resource_type' => 'task',
        ]], 200),
    ]);

    $resource = createTaskResource($mockClient);
    $result = $resource->getByCustomId('ws1', 'ENG-42');

    expect($result)->toBeInstanceOf(TaskData::class)
        ->and($result->gid)->toBe('t9');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/workspaces/ws1/tasks/custom_id/ENG-42');
});
