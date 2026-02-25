<?php

use WMBH\Asana\Data\AttachmentData;
use WMBH\Asana\Data\Shared\CompactResource;

test('AttachmentData can be created from array', function () {
    $data = AttachmentData::from([
        'gid' => '700',
        'resource_type' => 'attachment',
        'name' => 'design.png',
        'resource_subtype' => 'asana',
    ]);

    expect($data->gid)->toBe('700')
        ->and($data->name)->toBe('design.png')
        ->and($data->resource_subtype)->toBe('asana');
});

test('AttachmentData handles null optional fields', function () {
    $data = AttachmentData::from(['gid' => '700']);

    expect($data->gid)->toBe('700')
        ->and($data->name)->toBeNull()
        ->and($data->download_url)->toBeNull()
        ->and($data->host)->toBeNull()
        ->and($data->parent)->toBeNull()
        ->and($data->size)->toBeNull()
        ->and($data->view_url)->toBeNull();
});

test('AttachmentData casts nested parent to CompactResource', function () {
    $data = AttachmentData::from([
        'gid' => '700',
        'parent' => ['gid' => '123', 'name' => 'Task', 'resource_type' => 'task'],
    ]);

    expect($data->parent)->toBeInstanceOf(CompactResource::class)
        ->and($data->parent->gid)->toBe('123');
});

test('AttachmentData handles full payload', function () {
    $data = AttachmentData::from([
        'gid' => '700',
        'resource_type' => 'attachment',
        'name' => 'report.pdf',
        'resource_subtype' => 'external',
        'download_url' => 'https://example.com/report.pdf',
        'permanent_url' => 'https://example.com/perm/report.pdf',
        'size' => 54321,
        'view_url' => 'https://example.com/view/report.pdf',
        'host' => 'external',
        'created_at' => '2024-01-01T00:00:00.000Z',
    ]);

    expect($data->download_url)->toBe('https://example.com/report.pdf')
        ->and($data->size)->toBe(54321)
        ->and($data->permanent_url)->toBe('https://example.com/perm/report.pdf');
});
