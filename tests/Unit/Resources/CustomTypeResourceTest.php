<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\CustomTypeData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Resources\CustomTypeResource;

function createCustomTypeResource(MockClient $mockClient): CustomTypeResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new CustomTypeResource($connector);
}

test('list returns PaginatedResponse filtered by project', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Bug', 'resource_type' => 'custom_type'],
                ['gid' => '2', 'name' => 'Feature', 'resource_type' => 'custom_type'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createCustomTypeResource($mockClient);
    $result = $resource->list(projectGid: 'proj1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(CustomTypeData::class)
        ->and($result->data[0]->name)->toBe('Bug')
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/custom_types'
            && $request->query()->all() === ['project' => 'proj1'];
    });
});

test('list passes workspace, pagination and opt_fields', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'resource_type' => 'custom_type']],
            'next_page' => ['offset' => 'abc', 'uri' => '/custom_types?offset=abc'],
        ], 200),
    ]);

    $resource = createCustomTypeResource($mockClient);
    $result = $resource->list(workspaceGid: 'ws1', optFields: ['name', 'status_options'], offset: 'off1', limit: 25);

    expect($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('abc');

    $mockClient->assertSent(function ($request) {
        return $request->query()->all() === [
            'workspace' => 'ws1',
            'opt_fields' => 'name,status_options',
            'offset' => 'off1',
            'limit' => 25,
        ];
    });
});

test('get returns CustomTypeData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1000',
            'resource_type' => 'custom_type',
            'name' => 'Bug',
        ]], 200),
    ]);

    $resource = createCustomTypeResource($mockClient);
    $result = $resource->get('1000', ['name']);

    expect($result)->toBeInstanceOf(CustomTypeData::class)
        ->and($result->gid)->toBe('1000')
        ->and($result->name)->toBe('Bug');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/custom_types/1000'
            && $request->query()->all() === ['opt_fields' => 'name'];
    });
});
