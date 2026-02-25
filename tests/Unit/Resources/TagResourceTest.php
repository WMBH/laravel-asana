<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\TagData;
use WMBH\Asana\Resources\TagResource;

function createTagResource(MockClient $mockClient): TagResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new TagResource($connector);
}

test('get returns TagData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '300',
            'name' => 'Priority',
            'resource_type' => 'tag',
            'color' => 'red',
        ]], 200),
    ]);

    $resource = createTagResource($mockClient);
    $result = $resource->get('300');

    expect($result)->toBeInstanceOf(TagData::class)
        ->and($result->gid)->toBe('300')
        ->and($result->name)->toBe('Priority')
        ->and($result->color)->toBe('red');
});

test('getForTask returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'name' => 'Bug', 'resource_type' => 'tag']],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createTagResource($mockClient);
    $result = $resource->getForTask('task1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1)
        ->and($result->data[0])->toBeInstanceOf(TagData::class);
});

test('getForWorkspace returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Bug', 'resource_type' => 'tag'],
                ['gid' => '2', 'name' => 'Feature', 'resource_type' => 'tag'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createTagResource($mockClient);
    $result = $resource->getForWorkspace('ws1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2);
});

test('create returns TagData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '400',
            'name' => 'New Tag',
            'resource_type' => 'tag',
        ]], 200),
    ]);

    $resource = createTagResource($mockClient);
    $result = $resource->create(['name' => 'New Tag', 'workspace' => 'ws1']);

    expect($result)->toBeInstanceOf(TagData::class)
        ->and($result->gid)->toBe('400');
});

test('createForWorkspace returns TagData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '401',
            'name' => 'WS Tag',
            'resource_type' => 'tag',
        ]], 200),
    ]);

    $resource = createTagResource($mockClient);
    $result = $resource->createForWorkspace('ws1', ['name' => 'WS Tag']);

    expect($result)->toBeInstanceOf(TagData::class)
        ->and($result->gid)->toBe('401');
});

test('update returns TagData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '300',
            'name' => 'Updated Tag',
            'resource_type' => 'tag',
        ]], 200),
    ]);

    $resource = createTagResource($mockClient);
    $result = $resource->update('300', ['name' => 'Updated Tag']);

    expect($result)->toBeInstanceOf(TagData::class)
        ->and($result->name)->toBe('Updated Tag');
});

test('delete returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createTagResource($mockClient);

    expect($resource->delete('300'))->toBeTrue();
});
