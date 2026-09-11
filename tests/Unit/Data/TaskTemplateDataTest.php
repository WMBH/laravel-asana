<?php

use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\TaskTemplateData;

test('TaskTemplateData can be created from array', function () {
    $data = TaskTemplateData::from([
        'gid' => '1',
        'resource_type' => 'task_template',
        'name' => 'Bug report',
        'created_at' => '2026-01-01T00:00:00.000Z',
    ]);

    expect($data->gid)->toBe('1')
        ->and($data->resource_type)->toBe('task_template')
        ->and($data->name)->toBe('Bug report')
        ->and($data->created_at)->toBe('2026-01-01T00:00:00.000Z');
});

test('TaskTemplateData handles null optional fields', function () {
    $data = TaskTemplateData::from(['gid' => '1']);

    expect($data->gid)->toBe('1')
        ->and($data->name)->toBeNull()
        ->and($data->project)->toBeNull()
        ->and($data->template)->toBeNull()
        ->and($data->created_by)->toBeNull()
        ->and($data->created_at)->toBeNull();
});

test('TaskTemplateData casts nested project and created_by to CompactResource', function () {
    $data = TaskTemplateData::from([
        'gid' => '1',
        'project' => ['gid' => '10', 'name' => 'Engineering', 'resource_type' => 'project'],
        'created_by' => ['gid' => '20', 'name' => 'Jane', 'resource_type' => 'user'],
        'template' => ['name' => 'Bug report', 'notes' => 'Steps to reproduce'],
    ]);

    expect($data->project)->toBeInstanceOf(CompactResource::class)
        ->and($data->project->gid)->toBe('10')
        ->and($data->created_by)->toBeInstanceOf(CompactResource::class)
        ->and($data->created_by->name)->toBe('Jane')
        ->and($data->template)->toBe(['name' => 'Bug report', 'notes' => 'Steps to reproduce']);
});
