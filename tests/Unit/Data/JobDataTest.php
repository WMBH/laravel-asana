<?php

use WMBH\Asana\Data\JobData;
use WMBH\Asana\Data\Shared\CompactResource;

test('JobData can be created from array', function () {
    $data = JobData::from([
        'gid' => '777',
        'resource_type' => 'job',
        'resource_subtype' => 'instantiate_task',
        'status' => 'in_progress',
    ]);

    expect($data->gid)->toBe('777')
        ->and($data->resource_type)->toBe('job')
        ->and($data->resource_subtype)->toBe('instantiate_task')
        ->and($data->status)->toBe('in_progress');
});

test('JobData handles null optional fields', function () {
    $data = JobData::from(['gid' => '777']);

    expect($data->status)->toBeNull()
        ->and($data->new_task)->toBeNull()
        ->and($data->new_project)->toBeNull()
        ->and($data->new_portfolio)->toBeNull()
        ->and($data->new_project_template)->toBeNull()
        ->and($data->new_graph_export)->toBeNull()
        ->and($data->new_resource_export)->toBeNull();
});

test('JobData casts new_task and new_project to CompactResource', function () {
    $data = JobData::from([
        'gid' => '777',
        'status' => 'succeeded',
        'new_task' => ['gid' => '1', 'name' => 'From template', 'resource_type' => 'task'],
        'new_project' => ['gid' => '2', 'name' => 'New project', 'resource_type' => 'project'],
    ]);

    expect($data->new_task)->toBeInstanceOf(CompactResource::class)
        ->and($data->new_task->gid)->toBe('1')
        ->and($data->new_project)->toBeInstanceOf(CompactResource::class)
        ->and($data->new_project->name)->toBe('New project');
});

test('JobData keeps export payloads as arrays', function () {
    $data = JobData::from([
        'gid' => '777',
        'new_graph_export' => ['gid' => '9', 'download_url' => 'https://example.test/x.csv'],
    ]);

    expect($data->new_graph_export)->toBe(['gid' => '9', 'download_url' => 'https://example.test/x.csv']);
});
