<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\CustomFieldData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Resources\CustomFieldResource;

function createCustomFieldResource(MockClient $mockClient): CustomFieldResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new CustomFieldResource($connector);
}

test('get returns CustomFieldData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '900',
            'name' => 'Priority',
            'resource_type' => 'custom_field',
            'resource_subtype' => 'enum',
            'type' => 'enum',
        ]], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->get('900');

    expect($result)->toBeInstanceOf(CustomFieldData::class)
        ->and($result->gid)->toBe('900')
        ->and($result->name)->toBe('Priority')
        ->and($result->resource_subtype)->toBe('enum');
});

test('getForWorkspace returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Priority', 'resource_type' => 'custom_field'],
                ['gid' => '2', 'name' => 'Points', 'resource_type' => 'custom_field'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->getForWorkspace('ws1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(CustomFieldData::class);
});

test('create returns CustomFieldData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '901',
            'name' => 'Story Points',
            'resource_type' => 'custom_field',
            'resource_subtype' => 'number',
        ]], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->create([
        'name' => 'Story Points',
        'resource_subtype' => 'number',
        'workspace' => 'ws1',
    ]);

    expect($result)->toBeInstanceOf(CustomFieldData::class)
        ->and($result->gid)->toBe('901');
});

test('update returns CustomFieldData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '900',
            'name' => 'Updated Field',
            'resource_type' => 'custom_field',
        ]], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->update('900', ['name' => 'Updated Field']);

    expect($result)->toBeInstanceOf(CustomFieldData::class)
        ->and($result->name)->toBe('Updated Field');
});

test('delete returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);

    expect($resource->delete('900'))->toBeTrue();
});
