<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\StatusUpdateData;
use WMBH\Asana\Resources\StatusUpdateResource;

function createStatusUpdateResource(MockClient $mockClient): StatusUpdateResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new StatusUpdateResource($connector);
}

test('get returns StatusUpdateData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '700',
            'resource_type' => 'status_update',
            'title' => 'On track',
            'status_type' => 'on_track',
        ]], 200),
    ]);

    $resource = createStatusUpdateResource($mockClient);
    $result = $resource->get('700');

    expect($result)->toBeInstanceOf(StatusUpdateData::class)
        ->and($result->gid)->toBe('700')
        ->and($result->title)->toBe('On track')
        ->and($result->status_type)->toBe('on_track');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/status_updates/700';
    });
});

test('getForObject returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'title' => 'Update 1', 'resource_type' => 'status_update'],
                ['gid' => '2', 'title' => 'Update 2', 'resource_type' => 'status_update'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createStatusUpdateResource($mockClient);
    $result = $resource->getForObject('proj1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(StatusUpdateData::class)
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(function ($request) {
        $query = $request->query()->all();

        return $request->resolveEndpoint() === '/status_updates'
            && $query['parent'] === 'proj1'
            && ! isset($query['created_since']);
    });
});

test('getForObject passes pagination and created_since', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'resource_type' => 'status_update']],
            'next_page' => ['offset' => 'abc', 'uri' => '/status_updates?offset=abc'],
        ], 200),
    ]);

    $resource = createStatusUpdateResource($mockClient);
    $result = $resource->getForObject('proj1', ['title'], 'off1', 10, '2025-01-01T00:00:00Z');

    expect($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('abc');

    $mockClient->assertSent(function ($request) {
        $query = $request->query()->all();

        return $query['opt_fields'] === 'title'
            && $query['offset'] === 'off1'
            && $query['limit'] === 10
            && $query['created_since'] === '2025-01-01T00:00:00Z';
    });
});

test('create returns StatusUpdateData and sends parent in body', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '701',
            'resource_type' => 'status_update',
            'text' => 'Shipping Friday',
            'status_type' => 'on_track',
        ]], 201),
    ]);

    $resource = createStatusUpdateResource($mockClient);
    $result = $resource->create('proj1', ['text' => 'Shipping Friday', 'status_type' => 'on_track']);

    expect($result)->toBeInstanceOf(StatusUpdateData::class)
        ->and($result->gid)->toBe('701')
        ->and($result->text)->toBe('Shipping Friday');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/status_updates'
            && $request->body()->all() === ['data' => [
                'parent' => 'proj1',
                'text' => 'Shipping Friday',
                'status_type' => 'on_track',
            ]];
    });
});

test('delete returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createStatusUpdateResource($mockClient);

    expect($resource->delete('700'))->toBeTrue();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/status_updates/700';
    });
});
