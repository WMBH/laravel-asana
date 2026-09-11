<?php

use WMBH\Asana\Data\EventData;
use WMBH\Asana\Data\Shared\EventsResponse;

test('EventsResponse::fromResponse maps data, sync and has_more', function () {
    $result = EventsResponse::fromResponse([
        'data' => [
            ['type' => 'task', 'action' => 'added'],
            ['type' => 'task', 'action' => 'changed'],
        ],
        'sync' => 'tok456',
        'has_more' => true,
    ]);

    expect($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(EventData::class)
        ->and($result->data[1]->action)->toBe('changed')
        ->and($result->sync)->toBe('tok456')
        ->and($result->hasMore)->toBeTrue();
});

test('EventsResponse::fromResponse tolerates missing keys', function () {
    $result = EventsResponse::fromResponse([]);

    expect($result->data)->toBe([])
        ->and($result->sync)->toBeNull()
        ->and($result->hasMore)->toBeFalse();
});
