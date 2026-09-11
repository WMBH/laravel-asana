<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\ReactionData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Resources\ReactionResource;

function createReactionResource(MockClient $mockClient): ReactionResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new ReactionResource($connector);
}

test('getForObject returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'emoji' => '👍', 'user' => ['gid' => '111', 'resource_type' => 'user']],
                ['gid' => '2', 'emoji' => '👍🏽', 'user' => ['gid' => '222', 'resource_type' => 'user']],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createReactionResource($mockClient);
    $result = $resource->getForObject('task1', '👍');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(ReactionData::class)
        ->and($result->data[0]->user->gid)->toBe('111')
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/reactions'
            && $request->query()->all() === ['target' => 'task1', 'emoji_base' => '👍'];
    });
});

test('getForObject passes pagination', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'emoji' => '❤️']],
            'next_page' => ['offset' => 'abc', 'uri' => '/reactions?offset=abc'],
        ], 200),
    ]);

    $resource = createReactionResource($mockClient);
    $result = $resource->getForObject('task1', '❤️', 'off1', 50);

    expect($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('abc');

    $mockClient->assertSent(function ($request) {
        return $request->query()->all() === [
            'target' => 'task1',
            'emoji_base' => '❤️',
            'offset' => 'off1',
            'limit' => 50,
        ];
    });
});
