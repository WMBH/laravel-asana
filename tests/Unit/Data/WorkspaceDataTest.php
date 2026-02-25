<?php

use WMBH\Asana\Data\WorkspaceData;

test('WorkspaceData can be created from array', function () {
    $data = WorkspaceData::from([
        'gid' => '10',
        'resource_type' => 'workspace',
        'name' => 'My Workspace',
        'is_organization' => true,
    ]);

    expect($data->gid)->toBe('10')
        ->and($data->name)->toBe('My Workspace')
        ->and($data->is_organization)->toBeTrue();
});

test('WorkspaceData handles null optional fields', function () {
    $data = WorkspaceData::from(['gid' => '10']);

    expect($data->gid)->toBe('10')
        ->and($data->name)->toBeNull()
        ->and($data->is_organization)->toBeNull()
        ->and($data->email_domains)->toBeNull();
});

test('WorkspaceData handles email_domains array', function () {
    $data = WorkspaceData::from([
        'gid' => '10',
        'email_domains' => ['example.com', 'test.com'],
    ]);

    expect($data->email_domains)->toBeArray()->toHaveCount(2);
});
