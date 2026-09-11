<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\AccessRequestData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Resources\AccessRequestResource;

function createAccessRequestResource(MockClient $mockClient): AccessRequestResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new AccessRequestResource($connector);
}

test('list returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'resource_type' => 'access_request', 'approval_status' => 'pending'],
                ['gid' => '2', 'resource_type' => 'access_request', 'approval_status' => 'pending'],
            ],
        ], 200),
    ]);

    $resource = createAccessRequestResource($mockClient);
    $result = $resource->list('proj1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(AccessRequestData::class)
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/access_requests'
            && $request->query()->all() === ['target' => 'proj1'];
    });
});

test('list passes user and opt_fields', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createAccessRequestResource($mockClient);
    $resource->list('proj1', 'user1', ['message']);

    $mockClient->assertSent(function ($request) {
        return $request->query()->all() === [
            'target' => 'proj1',
            'user' => 'user1',
            'opt_fields' => 'message',
        ];
    });
});

test('create returns AccessRequestData and sends target and message', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1201',
            'resource_type' => 'access_request',
            'message' => 'Please add me',
            'approval_status' => 'pending',
        ]], 201),
    ]);

    $resource = createAccessRequestResource($mockClient);
    $result = $resource->create('proj1', 'Please add me');

    expect($result)->toBeInstanceOf(AccessRequestData::class)
        ->and($result->gid)->toBe('1201')
        ->and($result->message)->toBe('Please add me');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/access_requests'
            && $request->body()->all() === ['data' => ['target' => 'proj1', 'message' => 'Please add me']];
    });
});

test('create omits message when null', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => ['gid' => '1202', 'resource_type' => 'access_request']], 201),
    ]);

    $resource = createAccessRequestResource($mockClient);
    $resource->create('proj1');

    $mockClient->assertSent(function ($request) {
        return $request->body()->all() === ['data' => ['target' => 'proj1']];
    });
});

test('approve returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createAccessRequestResource($mockClient);

    expect($resource->approve('1200'))->toBeTrue();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/access_requests/1200/approve';
    });
});

test('reject returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createAccessRequestResource($mockClient);

    expect($resource->reject('1200'))->toBeTrue();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/access_requests/1200/reject';
    });
});
