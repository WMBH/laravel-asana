<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\GoalData;
use WMBH\Asana\Data\Shared\CompactResource;
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

test('getSubgoals maps goal relationships to subgoal CompactResources', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                [
                    'gid' => '900',
                    'resource_type' => 'goal_relationship',
                    'resource_subtype' => 'subgoal',
                    'supporting_resource' => ['gid' => '10', 'name' => 'Subgoal', 'resource_type' => 'goal'],
                ],
            ],
            'next_page' => ['offset' => 'tok', 'uri' => '/goal_relationships?offset=tok'],
        ], 200),
    ]);

    $resource = createGoalResource($mockClient);
    $result = $resource->getSubgoals('1100');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1)
        ->and($result->data[0])->toBeInstanceOf(CompactResource::class)
        ->and($result->data[0]->gid)->toBe('10')
        ->and($result->data[0]->name)->toBe('Subgoal')
        ->and($result->nextPageToken)->toBe('tok');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/goal_relationships'
        && $request->query()->all() === ['supported_goal' => '1100', 'resource_subtype' => 'subgoal']);
});

test('getSubgoals returns an empty page when there are no relationships', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => null], 200),
    ]);

    $resource = createGoalResource($mockClient);
    $result = $resource->getSubgoals('1100');

    expect($result->data)->toBe([])
        ->and($result->hasNextPage())->toBeFalse();
});

test('addSubgoal posts a supporting relationship', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => ['gid' => '900', 'resource_type' => 'goal_relationship']], 200),
    ]);

    $resource = createGoalResource($mockClient);

    expect($resource->addSubgoal('1100', 'sub1'))->toBeTrue();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/goals/1100/addSupportingRelationship'
        && $request->body()->all() === ['data' => ['supporting_resource' => 'sub1']]);
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
