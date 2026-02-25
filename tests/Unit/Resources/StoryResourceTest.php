<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\StoryData;
use WMBH\Asana\Resources\StoryResource;

function createStoryResource(MockClient $mockClient): StoryResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new StoryResource($connector);
}

test('get returns StoryData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '500',
            'text' => 'A comment',
            'resource_type' => 'story',
            'type' => 'comment',
        ]], 200),
    ]);

    $resource = createStoryResource($mockClient);
    $result = $resource->get('500');

    expect($result)->toBeInstanceOf(StoryData::class)
        ->and($result->gid)->toBe('500')
        ->and($result->text)->toBe('A comment')
        ->and($result->type)->toBe('comment');
});

test('getForTask returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'text' => 'Comment 1', 'resource_type' => 'story'],
                ['gid' => '2', 'text' => 'Comment 2', 'resource_type' => 'story'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createStoryResource($mockClient);
    $result = $resource->getForTask('task1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(StoryData::class);
});

test('create returns StoryData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '600',
            'text' => 'New comment',
            'resource_type' => 'story',
        ]], 200),
    ]);

    $resource = createStoryResource($mockClient);
    $result = $resource->create('task1', ['text' => 'New comment']);

    expect($result)->toBeInstanceOf(StoryData::class)
        ->and($result->gid)->toBe('600')
        ->and($result->text)->toBe('New comment');
});

test('update returns StoryData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '500',
            'text' => 'Updated comment',
            'resource_type' => 'story',
            'is_pinned' => true,
        ]], 200),
    ]);

    $resource = createStoryResource($mockClient);
    $result = $resource->update('500', ['is_pinned' => true]);

    expect($result)->toBeInstanceOf(StoryData::class)
        ->and($result->is_pinned)->toBeTrue();
});

test('delete returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createStoryResource($mockClient);

    expect($resource->delete('500'))->toBeTrue();
});
