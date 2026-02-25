<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\AttachmentData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Resources\AttachmentResource;

function createAttachmentResource(MockClient $mockClient): AttachmentResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new AttachmentResource($connector);
}

test('get returns AttachmentData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '700',
            'name' => 'design.png',
            'resource_type' => 'attachment',
            'resource_subtype' => 'asana',
            'download_url' => 'https://example.com/design.png',
            'size' => 12345,
        ]], 200),
    ]);

    $resource = createAttachmentResource($mockClient);
    $result = $resource->get('700');

    expect($result)->toBeInstanceOf(AttachmentData::class)
        ->and($result->gid)->toBe('700')
        ->and($result->name)->toBe('design.png')
        ->and($result->download_url)->toBe('https://example.com/design.png')
        ->and($result->size)->toBe(12345);
});

test('getForTask returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'file1.pdf', 'resource_type' => 'attachment'],
                ['gid' => '2', 'name' => 'file2.pdf', 'resource_type' => 'attachment'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createAttachmentResource($mockClient);
    $result = $resource->getForTask('task1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(AttachmentData::class);
});

test('create returns AttachmentData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '800',
            'name' => 'spec.pdf',
            'resource_type' => 'attachment',
            'resource_subtype' => 'external',
        ]], 200),
    ]);

    $resource = createAttachmentResource($mockClient);
    $result = $resource->create('task1', [
        'resource_subtype' => 'external',
        'name' => 'spec.pdf',
        'url' => 'https://example.com/spec.pdf',
    ]);

    expect($result)->toBeInstanceOf(AttachmentData::class)
        ->and($result->gid)->toBe('800')
        ->and($result->resource_subtype)->toBe('external');
});

test('delete returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createAttachmentResource($mockClient);

    expect($resource->delete('700'))->toBeTrue();
});
