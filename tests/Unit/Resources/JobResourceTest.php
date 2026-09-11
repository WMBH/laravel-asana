<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\JobData;
use WMBH\Asana\Resources\JobResource;

function createJobResource(MockClient $mockClient): JobResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new JobResource($connector);
}

test('get returns JobData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => 'job1',
            'resource_type' => 'job',
            'resource_subtype' => 'duplicate_task',
            'status' => 'succeeded',
            'new_task' => ['gid' => 't2', 'name' => 'Copy of task', 'resource_type' => 'task'],
        ]], 200),
    ]);

    $resource = createJobResource($mockClient);
    $result = $resource->get('job1');

    expect($result)->toBeInstanceOf(JobData::class)
        ->and($result->gid)->toBe('job1')
        ->and($result->status)->toBe('succeeded')
        ->and($result->new_task->gid)->toBe('t2');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/jobs/job1'
        && $request->query()->all() === []);
});

test('get forwards opt_fields', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => ['gid' => 'job1', 'resource_type' => 'job', 'status' => 'in_progress']], 200),
    ]);

    $resource = createJobResource($mockClient);
    $resource->get('job1', ['status', 'new_task']);

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === ['opt_fields' => 'status,new_task']);
});
