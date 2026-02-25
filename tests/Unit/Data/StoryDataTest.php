<?php

use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\StoryData;

test('StoryData can be created from array', function () {
    $data = StoryData::from([
        'gid' => '500',
        'resource_type' => 'story',
        'text' => 'A comment',
        'type' => 'comment',
    ]);

    expect($data->gid)->toBe('500')
        ->and($data->text)->toBe('A comment')
        ->and($data->type)->toBe('comment');
});

test('StoryData handles null optional fields', function () {
    $data = StoryData::from(['gid' => '500']);

    expect($data->gid)->toBe('500')
        ->and($data->text)->toBeNull()
        ->and($data->html_text)->toBeNull()
        ->and($data->type)->toBeNull()
        ->and($data->created_by)->toBeNull()
        ->and($data->target)->toBeNull()
        ->and($data->is_pinned)->toBeNull();
});

test('StoryData casts nested created_by to CompactResource', function () {
    $data = StoryData::from([
        'gid' => '500',
        'created_by' => ['gid' => '111', 'name' => 'John', 'resource_type' => 'user'],
    ]);

    expect($data->created_by)->toBeInstanceOf(CompactResource::class)
        ->and($data->created_by->gid)->toBe('111')
        ->and($data->created_by->name)->toBe('John');
});

test('StoryData casts nested target to CompactResource', function () {
    $data = StoryData::from([
        'gid' => '500',
        'target' => ['gid' => '123', 'name' => 'Task', 'resource_type' => 'task'],
    ]);

    expect($data->target)->toBeInstanceOf(CompactResource::class)
        ->and($data->target->gid)->toBe('123');
});
