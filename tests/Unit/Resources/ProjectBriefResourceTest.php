<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\ProjectBriefData;
use WMBH\Asana\Resources\ProjectBriefResource;

function createProjectBriefResource(MockClient $mockClient): ProjectBriefResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new ProjectBriefResource($connector);
}

test('get returns ProjectBriefData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '800',
            'resource_type' => 'project_brief',
            'title' => 'Launch plan',
        ]], 200),
    ]);

    $resource = createProjectBriefResource($mockClient);
    $result = $resource->get('800', ['title']);

    expect($result)->toBeInstanceOf(ProjectBriefData::class)
        ->and($result->gid)->toBe('800')
        ->and($result->title)->toBe('Launch plan');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/project_briefs/800'
            && $request->query()->all() === ['opt_fields' => 'title'];
    });
});

test('create returns ProjectBriefData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '801',
            'resource_type' => 'project_brief',
            'title' => 'New brief',
            'text' => 'Body',
        ]], 201),
    ]);

    $resource = createProjectBriefResource($mockClient);
    $result = $resource->create('proj1', ['title' => 'New brief', 'text' => 'Body']);

    expect($result)->toBeInstanceOf(ProjectBriefData::class)
        ->and($result->gid)->toBe('801')
        ->and($result->title)->toBe('New brief');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/projects/proj1/project_briefs'
            && $request->body()->all() === ['data' => ['title' => 'New brief', 'text' => 'Body']];
    });
});

test('update returns ProjectBriefData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '800',
            'resource_type' => 'project_brief',
            'title' => 'Renamed',
        ]], 200),
    ]);

    $resource = createProjectBriefResource($mockClient);
    $result = $resource->update('800', ['title' => 'Renamed']);

    expect($result)->toBeInstanceOf(ProjectBriefData::class)
        ->and($result->title)->toBe('Renamed');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/project_briefs/800'
            && $request->body()->all() === ['data' => ['title' => 'Renamed']];
    });
});

test('delete returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createProjectBriefResource($mockClient);

    expect($resource->delete('800'))->toBeTrue();

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/project_briefs/800';
    });
});
