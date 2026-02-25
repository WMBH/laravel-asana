<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Resources\BatchResource;

function createBatchResource(MockClient $mockClient): BatchResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new BatchResource($connector);
}

test('submit returns array of results', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            ['status_code' => 200, 'body' => ['data' => ['gid' => '1', 'name' => 'Task 1']]],
            ['status_code' => 200, 'body' => ['data' => ['gid' => '2', 'name' => 'Task 2']]],
        ]], 200),
    ]);

    $resource = createBatchResource($mockClient);
    $result = $resource->submit([
        ['relative_path' => '/tasks/1', 'method' => 'GET'],
        ['relative_path' => '/tasks/2', 'method' => 'GET'],
    ]);

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(2)
        ->and($result[0]['status_code'])->toBe(200)
        ->and($result[0]['body']['data']['gid'])->toBe('1');
});

test('submit handles mixed success and failure', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            ['status_code' => 200, 'body' => ['data' => ['gid' => '1']]],
            ['status_code' => 404, 'body' => ['errors' => [['message' => 'Not Found']]]],
        ]], 200),
    ]);

    $resource = createBatchResource($mockClient);
    $result = $resource->submit([
        ['relative_path' => '/tasks/1', 'method' => 'GET'],
        ['relative_path' => '/tasks/invalid', 'method' => 'GET'],
    ]);

    expect($result)->toHaveCount(2)
        ->and($result[0]['status_code'])->toBe(200)
        ->and($result[1]['status_code'])->toBe(404);
});
