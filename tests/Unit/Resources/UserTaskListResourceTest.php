<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\UserTaskListData;
use WMBH\Asana\Resources\UserTaskListResource;

function createUserTaskListResource(MockClient $mockClient): UserTaskListResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new UserTaskListResource($connector);
}

test('get returns UserTaskListData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1100',
            'resource_type' => 'user_task_list',
            'name' => 'My Tasks',
        ]], 200),
    ]);

    $resource = createUserTaskListResource($mockClient);
    $result = $resource->get('1100', ['name']);

    expect($result)->toBeInstanceOf(UserTaskListData::class)
        ->and($result->gid)->toBe('1100')
        ->and($result->name)->toBe('My Tasks');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/user_task_lists/1100'
            && $request->query()->all() === ['opt_fields' => 'name'];
    });
});

test('getForUser returns UserTaskListData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1100',
            'resource_type' => 'user_task_list',
            'name' => 'My Tasks',
            'owner' => ['gid' => 'me', 'resource_type' => 'user'],
        ]], 200),
    ]);

    $resource = createUserTaskListResource($mockClient);
    $result = $resource->getForUser('me', 'ws1');

    expect($result)->toBeInstanceOf(UserTaskListData::class)
        ->and($result->gid)->toBe('1100')
        ->and($result->owner->gid)->toBe('me');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/users/me/user_task_list'
            && $request->query()->all() === ['workspace' => 'ws1'];
    });
});
