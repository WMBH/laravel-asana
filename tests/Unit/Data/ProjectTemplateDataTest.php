<?php

use WMBH\Asana\Data\ProjectTemplateData;
use WMBH\Asana\Data\Shared\CompactResource;

test('ProjectTemplateData can be created from array', function () {
    $data = ProjectTemplateData::from([
        'gid' => '1',
        'resource_type' => 'project_template',
        'name' => 'Sprint',
        'description' => 'Two-week sprint',
        'html_description' => '<body>Two-week sprint</body>',
        'public' => true,
        'color' => 'light-green',
    ]);

    expect($data->gid)->toBe('1')
        ->and($data->resource_type)->toBe('project_template')
        ->and($data->name)->toBe('Sprint')
        ->and($data->description)->toBe('Two-week sprint')
        ->and($data->html_description)->toBe('<body>Two-week sprint</body>')
        ->and($data->public)->toBeTrue()
        ->and($data->color)->toBe('light-green');
});

test('ProjectTemplateData handles null optional fields', function () {
    $data = ProjectTemplateData::from(['gid' => '1']);

    expect($data->gid)->toBe('1')
        ->and($data->name)->toBeNull()
        ->and($data->public)->toBeNull()
        ->and($data->owner)->toBeNull()
        ->and($data->team)->toBeNull()
        ->and($data->requested_dates)->toBeNull()
        ->and($data->requested_roles)->toBeNull();
});

test('ProjectTemplateData casts nested owner and team to CompactResource', function () {
    $data = ProjectTemplateData::from([
        'gid' => '1',
        'owner' => ['gid' => '20', 'name' => 'Jane', 'resource_type' => 'user'],
        'team' => ['gid' => '30', 'name' => 'Engineering', 'resource_type' => 'team'],
        'requested_dates' => [['gid' => '40', 'name' => 'Start date']],
        'requested_roles' => [['gid' => '50', 'name' => 'Lead']],
    ]);

    expect($data->owner)->toBeInstanceOf(CompactResource::class)
        ->and($data->owner->gid)->toBe('20')
        ->and($data->team)->toBeInstanceOf(CompactResource::class)
        ->and($data->team->name)->toBe('Engineering')
        ->and($data->requested_dates)->toHaveCount(1)
        ->and($data->requested_roles[0]['name'])->toBe('Lead');
});
