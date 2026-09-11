<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\JobData;
use WMBH\Asana\Data\ProjectTemplateData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\ProjectTemplates\InstantiateProjectRequest;
use WMBH\Asana\Resources\ProjectTemplateResource;

function createProjectTemplateResource(MockClient $mockClient): ProjectTemplateResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new ProjectTemplateResource($connector);
}

test('list returns PaginatedResponse of ProjectTemplateData', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Sprint', 'resource_type' => 'project_template'],
                ['gid' => '2', 'name' => 'Launch', 'resource_type' => 'project_template'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createProjectTemplateResource($mockClient);
    $result = $resource->list('ws1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(ProjectTemplateData::class)
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/project_templates'
        && $request->query()->all() === ['workspace' => 'ws1']);
});

test('list forwards team, opt_fields, offset and limit', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => null], 200),
    ]);

    $resource = createProjectTemplateResource($mockClient);
    $resource->list(null, 'team1', ['name'], 'abc', 10);

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === [
        'team' => 'team1',
        'opt_fields' => 'name',
        'offset' => 'abc',
        'limit' => 10,
    ]);
});

test('getForTeam returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'name' => 'Sprint', 'resource_type' => 'project_template']],
            'next_page' => ['offset' => 'tok', 'uri' => '/teams/team1/project_templates?offset=tok'],
        ], 200),
    ]);

    $resource = createProjectTemplateResource($mockClient);
    $result = $resource->getForTeam('team1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data[0])->toBeInstanceOf(ProjectTemplateData::class)
        ->and($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/teams/team1/project_templates');
});

test('get returns ProjectTemplateData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1',
            'name' => 'Sprint',
            'resource_type' => 'project_template',
        ]], 200),
    ]);

    $resource = createProjectTemplateResource($mockClient);
    $result = $resource->get('1', ['name']);

    expect($result)->toBeInstanceOf(ProjectTemplateData::class)
        ->and($result->gid)->toBe('1');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/project_templates/1'
        && $request->query()->all() === ['opt_fields' => 'name']);
});

test('delete returns true on 200', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createProjectTemplateResource($mockClient);

    expect($resource->delete('1'))->toBeTrue();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/project_templates/1');
});

test('instantiate returns JobData and sends data envelope', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => 'job1',
            'resource_type' => 'job',
            'resource_subtype' => 'instantiate_project',
            'status' => 'in_progress',
            'new_project' => ['gid' => 'p1', 'name' => 'Sprint 42', 'resource_type' => 'project'],
        ]], 201),
    ]);

    $resource = createProjectTemplateResource($mockClient);
    $result = $resource->instantiate('1', ['name' => 'Sprint 42', 'team' => 'team1', 'public' => false]);

    expect($result)->toBeInstanceOf(JobData::class)
        ->and($result->gid)->toBe('job1')
        ->and($result->new_project->gid)->toBe('p1');

    $mockClient->assertSent(fn (Request $request) => $request instanceof InstantiateProjectRequest
        && $request->resolveEndpoint() === '/project_templates/1/instantiateProject'
        && $request->body()->all() === ['data' => ['name' => 'Sprint 42', 'team' => 'team1', 'public' => false]]);
});
