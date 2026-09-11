<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\JobData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\TaskTemplateData;
use WMBH\Asana\Requests\TaskTemplates\InstantiateTaskRequest;
use WMBH\Asana\Resources\TaskTemplateResource;

function createTaskTemplateResource(MockClient $mockClient): TaskTemplateResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new TaskTemplateResource($connector);
}

test('list returns PaginatedResponse of TaskTemplateData', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Bug report', 'resource_type' => 'task_template'],
                ['gid' => '2', 'name' => 'Feature request', 'resource_type' => 'task_template'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createTaskTemplateResource($mockClient);
    $result = $resource->list('proj1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(TaskTemplateData::class)
        ->and($result->data[0]->gid)->toBe('1')
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/task_templates'
        && $request->query()->all() === ['project' => 'proj1']);
});

test('list forwards opt_fields, offset and limit', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => ['offset' => 'tok', 'uri' => '/task_templates?offset=tok']], 200),
    ]);

    $resource = createTaskTemplateResource($mockClient);
    $result = $resource->list('proj1', ['name', 'template'], 'abc', 50);

    expect($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === [
        'project' => 'proj1',
        'opt_fields' => 'name,template',
        'offset' => 'abc',
        'limit' => 50,
    ]);
});

test('get returns TaskTemplateData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1',
            'name' => 'Bug report',
            'resource_type' => 'task_template',
        ]], 200),
    ]);

    $resource = createTaskTemplateResource($mockClient);
    $result = $resource->get('1');

    expect($result)->toBeInstanceOf(TaskTemplateData::class)
        ->and($result->gid)->toBe('1')
        ->and($result->name)->toBe('Bug report');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/task_templates/1');
});

test('delete returns true on 200', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createTaskTemplateResource($mockClient);

    expect($resource->delete('1'))->toBeTrue();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/task_templates/1');
});

test('instantiate returns JobData and sends name', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => 'job1',
            'resource_type' => 'job',
            'resource_subtype' => 'instantiate_task',
            'status' => 'in_progress',
            'new_task' => ['gid' => 't1', 'name' => 'Bug: login', 'resource_type' => 'task'],
        ]], 201),
    ]);

    $resource = createTaskTemplateResource($mockClient);
    $result = $resource->instantiate('1', 'Bug: login');

    expect($result)->toBeInstanceOf(JobData::class)
        ->and($result->gid)->toBe('job1')
        ->and($result->status)->toBe('in_progress')
        ->and($result->new_task->gid)->toBe('t1');

    $mockClient->assertSent(fn (Request $request) => $request instanceof InstantiateTaskRequest
        && $request->resolveEndpoint() === '/task_templates/1/instantiateTask'
        && $request->body()->all() === ['data' => ['name' => 'Bug: login']]);
});

test('instantiate without name sends an empty data object', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => ['gid' => 'job1', 'resource_type' => 'job', 'status' => 'not_started']], 201),
    ]);

    $resource = createTaskTemplateResource($mockClient);
    $resource->instantiate('1');

    $mockClient->assertSent(fn (Request $request) => $request->body()->all()['data'] instanceof stdClass);
});
