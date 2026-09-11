<?php

use WMBH\Asana\Data\ProjectBriefData;
use WMBH\Asana\Data\Shared\CompactResource;

test('ProjectBriefData can be created from array', function () {
    $data = ProjectBriefData::from([
        'gid' => '800',
        'resource_type' => 'project_brief',
        'title' => 'Launch plan',
        'text' => 'We ship in Q3',
        'permalink_url' => 'https://app.asana.com/0/800',
    ]);

    expect($data->gid)->toBe('800')
        ->and($data->resource_type)->toBe('project_brief')
        ->and($data->title)->toBe('Launch plan')
        ->and($data->text)->toBe('We ship in Q3')
        ->and($data->permalink_url)->toBe('https://app.asana.com/0/800');
});

test('ProjectBriefData handles null optional fields', function () {
    $data = ProjectBriefData::from(['gid' => '800']);

    expect($data->gid)->toBe('800')
        ->and($data->title)->toBeNull()
        ->and($data->html_text)->toBeNull()
        ->and($data->text)->toBeNull()
        ->and($data->permalink_url)->toBeNull()
        ->and($data->project)->toBeNull();
});

test('ProjectBriefData casts nested project to CompactResource', function () {
    $data = ProjectBriefData::from([
        'gid' => '800',
        'project' => ['gid' => '900', 'name' => 'Project X', 'resource_type' => 'project'],
    ]);

    expect($data->project)->toBeInstanceOf(CompactResource::class)
        ->and($data->project->gid)->toBe('900')
        ->and($data->project->name)->toBe('Project X');
});
