<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\GoalData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Resources\GoalResource;

function createGoalResource(MockClient $mockClient): GoalResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new GoalResource($connector);
}

test('get returns GoalData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1100',
            'name' => 'Ship v2.0',
            'resource_type' => 'goal',
            'status' => 'green',
        ]], 200),
    ]);

    $resource = createGoalResource($mockClient);
    $result = $resource->get('1100');

    expect($result)->toBeInstanceOf(GoalData::class)
        ->and($result->gid)->toBe('1100')
        ->and($result->name)->toBe('Ship v2.0')
        ->and($result->status)->toBe('green');
});

test('list returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Goal A', 'resource_type' => 'goal'],
                ['gid' => '2', 'name' => 'Goal B', 'resource_type' => 'goal'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createGoalResource($mockClient);
    $result = $resource->list(['workspace' => 'ws1']);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(GoalData::class);
});

test('create returns GoalData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1101',
            'name' => 'New Goal',
            'resource_type' => 'goal',
        ]], 200),
    ]);

    $resource = createGoalResource($mockClient);
    $result = $resource->create(['name' => 'New Goal', 'workspace' => 'ws1']);

    expect($result)->toBeInstanceOf(GoalData::class)
        ->and($result->gid)->toBe('1101');
});

test('update returns GoalData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1100',
            'name' => 'Updated Goal',
            'resource_type' => 'goal',
        ]], 200),
    ]);

    $resource = createGoalResource($mockClient);
    $result = $resource->update('1100', ['name' => 'Updated Goal']);

    expect($result)->toBeInstanceOf(GoalData::class)
        ->and($result->name)->toBe('Updated Goal');
});

test('delete returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createGoalResource($mockClient);

    expect($resource->delete('1100'))->toBeTrue();
});

test('getSubgoals returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '10', 'name' => 'Subgoal', 'resource_type' => 'goal']],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createGoalResource($mockClient);
    $result = $resource->getSubgoals('1100');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1);
});

test('addSubgoal returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createGoalResource($mockClient);

    expect($resource->addSubgoal('1100', 'sub1'))->toBeTrue();
});

test('getRelationships returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '20', 'name' => 'Project', 'resource_type' => 'project']],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createGoalResource($mockClient);
    $result = $resource->getRelationships('1100');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1);
});

test('updateMetric returns GoalData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1100',
            'name' => 'Ship v2.0',
            'resource_type' => 'goal',
        ]], 200),
    ]);

    $resource = createGoalResource($mockClient);
    $result = $resource->updateMetric('1100', ['current_number_value' => 75]);

    expect($result)->toBeInstanceOf(GoalData::class)
        ->and($result->gid)->toBe('1100');
});
