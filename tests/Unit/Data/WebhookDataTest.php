<?php

use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\WebhookData;

test('WebhookData can be created from array', function () {
    $data = WebhookData::from([
        'gid' => '1200',
        'resource_type' => 'webhook',
        'active' => true,
        'target' => 'https://example.com/webhook',
    ]);

    expect($data->gid)->toBe('1200')
        ->and($data->active)->toBeTrue()
        ->and($data->target)->toBe('https://example.com/webhook');
});

test('WebhookData handles null optional fields', function () {
    $data = WebhookData::from(['gid' => '1200']);

    expect($data->gid)->toBe('1200')
        ->and($data->active)->toBeNull()
        ->and($data->resource)->toBeNull()
        ->and($data->target)->toBeNull()
        ->and($data->last_failure_at)->toBeNull()
        ->and($data->last_success_at)->toBeNull()
        ->and($data->filters)->toBeNull();
});

test('WebhookData casts nested resource to CompactResource', function () {
    $data = WebhookData::from([
        'gid' => '1200',
        'resource' => ['gid' => '999', 'name' => 'My Project', 'resource_type' => 'project'],
    ]);

    expect($data->resource)->toBeInstanceOf(CompactResource::class)
        ->and($data->resource->gid)->toBe('999')
        ->and($data->resource->name)->toBe('My Project');
});

test('WebhookData handles full payload', function () {
    $data = WebhookData::from([
        'gid' => '1200',
        'resource_type' => 'webhook',
        'active' => true,
        'target' => 'https://example.com/hook',
        'created_at' => '2024-01-01T00:00:00.000Z',
        'last_failure_at' => '2024-01-15T00:00:00.000Z',
        'last_failure_content' => 'Connection refused',
        'last_success_at' => '2024-01-14T00:00:00.000Z',
        'filters' => [['resource_type' => 'task', 'action' => 'changed']],
    ]);

    expect($data->active)->toBeTrue()
        ->and($data->last_failure_content)->toBe('Connection refused')
        ->and($data->filters)->toBeArray()->toHaveCount(1);
});
