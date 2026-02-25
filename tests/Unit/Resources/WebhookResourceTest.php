<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\WebhookData;
use WMBH\Asana\Resources\WebhookResource;

function createWebhookResource(MockClient $mockClient): WebhookResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new WebhookResource($connector);
}

test('get returns WebhookData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1200',
            'resource_type' => 'webhook',
            'active' => true,
            'target' => 'https://example.com/webhook',
        ]], 200),
    ]);

    $resource = createWebhookResource($mockClient);
    $result = $resource->get('1200');

    expect($result)->toBeInstanceOf(WebhookData::class)
        ->and($result->gid)->toBe('1200')
        ->and($result->active)->toBeTrue()
        ->and($result->target)->toBe('https://example.com/webhook');
});

test('getForWorkspace returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'resource_type' => 'webhook', 'active' => true],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createWebhookResource($mockClient);
    $result = $resource->getForWorkspace('ws1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1)
        ->and($result->data[0])->toBeInstanceOf(WebhookData::class);
});

test('getForWorkspace with resource filter', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'resource_type' => 'webhook']],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createWebhookResource($mockClient);
    $result = $resource->getForWorkspace('ws1', 'project1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1);
});

test('create returns WebhookData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1201',
            'resource_type' => 'webhook',
            'active' => true,
            'target' => 'https://example.com/hook',
        ]], 200),
    ]);

    $resource = createWebhookResource($mockClient);
    $result = $resource->create([
        'resource' => 'project1',
        'target' => 'https://example.com/hook',
    ]);

    expect($result)->toBeInstanceOf(WebhookData::class)
        ->and($result->gid)->toBe('1201');
});

test('update returns WebhookData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1200',
            'resource_type' => 'webhook',
            'active' => true,
        ]], 200),
    ]);

    $resource = createWebhookResource($mockClient);
    $result = $resource->update('1200', ['filters' => []]);

    expect($result)->toBeInstanceOf(WebhookData::class)
        ->and($result->gid)->toBe('1200');
});

test('delete returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createWebhookResource($mockClient);

    expect($resource->delete('1200'))->toBeTrue();
});
