<?php

use Illuminate\Support\Collection;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\TaskData;
use WMBH\Asana\Query\TaskQueryBuilder;

function createQueryBuilder(MockClient $mockClient, string $workspaceGid = 'ws123'): TaskQueryBuilder
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new TaskQueryBuilder($connector, $workspaceGid);
}

test('builder chains correctly', function () {
    $connector = new AsanaConnector('test-token');
    $builder = new TaskQueryBuilder($connector, 'ws123');

    $result = $builder->assignee('me')->completed(false)->sortBy('due_on');

    expect($result)->toBeInstanceOf(TaskQueryBuilder::class);
});

test('get returns Collection of TaskData', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Task 1', 'resource_type' => 'task'],
                ['gid' => '2', 'name' => 'Task 2', 'resource_type' => 'task'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $builder = createQueryBuilder($mockClient);
    $result = $builder->assignee('me')->get();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(2)
        ->and($result->first())->toBeInstanceOf(TaskData::class)
        ->and($result->first()->gid)->toBe('1');
});

test('paginate returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Task 1', 'resource_type' => 'task'],
            ],
            'next_page' => ['offset' => 'next123', 'uri' => '/search?offset=next123'],
        ], 200),
    ]);

    $builder = createQueryBuilder($mockClient);
    $result = $builder->paginate();

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('next123');
});

test('where maps field names correctly', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [],
            'next_page' => null,
        ], 200),
    ]);

    $builder = createQueryBuilder($mockClient);
    $builder->where('assignee', 'me')->where('completed', false)->get();

    $mockClient->assertSent(function ($request) {
        $query = $request->query()->all();

        return isset($query['assignee.any']) && $query['assignee.any'] === 'me'
            && isset($query['completed']) && $query['completed'] === false;
    });
});

test('fields sets opt_fields', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [],
            'next_page' => null,
        ], 200),
    ]);

    $builder = createQueryBuilder($mockClient);
    $builder->fields('name', 'completed', 'due_on')->get();

    $mockClient->assertSent(function ($request) {
        $query = $request->query()->all();

        return isset($query['opt_fields']) && $query['opt_fields'] === 'name,completed,due_on';
    });
});

test('limit sets limit param', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [],
            'next_page' => null,
        ], 200),
    ]);

    $builder = createQueryBuilder($mockClient);
    $builder->limit(25)->get();

    $mockClient->assertSent(function ($request) {
        $query = $request->query()->all();

        return isset($query['limit']) && $query['limit'] === 25;
    });
});

test('all fluent methods return the builder', function () {
    $connector = new AsanaConnector('test-token');
    $builder = new TaskQueryBuilder($connector, 'ws123');

    expect($builder->assignee('me'))->toBeInstanceOf(TaskQueryBuilder::class)
        ->and($builder->project('proj1'))->toBeInstanceOf(TaskQueryBuilder::class)
        ->and($builder->section('sec1'))->toBeInstanceOf(TaskQueryBuilder::class)
        ->and($builder->tag('tag1'))->toBeInstanceOf(TaskQueryBuilder::class)
        ->and($builder->completed(true))->toBeInstanceOf(TaskQueryBuilder::class)
        ->and($builder->modifiedSince('2024-01-01'))->toBeInstanceOf(TaskQueryBuilder::class)
        ->and($builder->dueOn('2024-01-01'))->toBeInstanceOf(TaskQueryBuilder::class)
        ->and($builder->dueBefore('2024-01-01'))->toBeInstanceOf(TaskQueryBuilder::class)
        ->and($builder->dueAfter('2024-01-01'))->toBeInstanceOf(TaskQueryBuilder::class)
        ->and($builder->sortBy('due_on'))->toBeInstanceOf(TaskQueryBuilder::class)
        ->and($builder->fields('name'))->toBeInstanceOf(TaskQueryBuilder::class)
        ->and($builder->limit(10))->toBeInstanceOf(TaskQueryBuilder::class);
});
