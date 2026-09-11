<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\EventData;
use WMBH\Asana\Data\Shared\EventsResponse;
use WMBH\Asana\Exceptions\AsanaException;
use WMBH\Asana\Resources\EventResource;

function createEventResource(MockClient $mockClient): EventResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new EventResource($connector);
}

test('get returns EventsResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['type' => 'task', 'action' => 'changed', 'resource' => ['gid' => '1', 'resource_type' => 'task']],
            ],
            'sync' => 'tok789',
            'has_more' => false,
        ], 200),
    ]);

    $resource = createEventResource($mockClient);
    $result = $resource->get('proj1', 'tok123', ['resource.name']);

    expect($result)->toBeInstanceOf(EventsResponse::class)
        ->and($result->data)->toHaveCount(1)
        ->and($result->data[0])->toBeInstanceOf(EventData::class)
        ->and($result->data[0]->action)->toBe('changed')
        ->and($result->sync)->toBe('tok789')
        ->and($result->hasMore)->toBeFalse();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/events'
            && $request->query()->all() === [
                'resource' => 'proj1',
                'sync' => 'tok123',
                'opt_fields' => 'resource.name',
            ];
    });
});

test('get without sync token returns the fresh token from a 412 response', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'errors' => [['message' => 'Sync token invalid or too old']],
            'sync' => 'tok123',
        ], 412),
    ]);

    $resource = createEventResource($mockClient);
    $result = $resource->get('proj1');

    expect($result)->toBeInstanceOf(EventsResponse::class)
        ->and($result->data)->toBe([])
        ->and($result->sync)->toBe('tok123')
        ->and($result->hasMore)->toBeFalse();

    $mockClient->assertSent(function ($request) {
        return $request->query()->all() === ['resource' => 'proj1'];
    });
});

test('get rethrows non-412 errors', function () {
    $mockClient = new MockClient([
        MockResponse::make(['errors' => [['message' => 'Server Error']]], 500),
    ]);

    $resource = createEventResource($mockClient);
    $resource->get('proj1', 'tok123');
})->throws(AsanaException::class, 'Server Error');

test('getForWorkspace returns EventsResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['type' => 'project', 'action' => 'added']],
            'sync' => 'tok999',
            'has_more' => true,
        ], 200),
    ]);

    $resource = createEventResource($mockClient);
    $result = $resource->getForWorkspace('ws1', 'tok123');

    expect($result)->toBeInstanceOf(EventsResponse::class)
        ->and($result->data[0]->type)->toBe('project')
        ->and($result->sync)->toBe('tok999')
        ->and($result->hasMore)->toBeTrue();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/workspaces/ws1/events'
            && $request->query()->all() === ['sync' => 'tok123'];
    });
});

test('getForWorkspace without sync token returns the fresh token from a 412 response', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'errors' => [['message' => 'Sync token invalid or too old']],
            'sync' => 'tok123',
        ], 412),
    ]);

    $resource = createEventResource($mockClient);
    $result = $resource->getForWorkspace('ws1');

    expect($result->data)->toBe([])
        ->and($result->sync)->toBe('tok123')
        ->and($result->hasMore)->toBeFalse();
});

test('getForWorkspace rethrows non-412 errors', function () {
    $mockClient = new MockClient([
        MockResponse::make(['errors' => [['message' => 'Server Error']]], 500),
    ]);

    $resource = createEventResource($mockClient);
    $resource->getForWorkspace('ws1', 'tok123');
})->throws(AsanaException::class, 'Server Error');
