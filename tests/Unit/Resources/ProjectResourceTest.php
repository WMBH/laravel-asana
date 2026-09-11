<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\JobData;
use WMBH\Asana\Data\ProjectData;
use WMBH\Asana\Data\ProjectMembershipData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\Projects\SaveProjectAsTemplateRequest;
use WMBH\Asana\Resources\ProjectResource;

function createProjectResource(MockClient $mockClient): ProjectResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new ProjectResource($connector);
}

test('get returns ProjectData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '789',
            'name' => 'Test Project',
            'resource_type' => 'project',
            'archived' => false,
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->get('789');

    expect($result)->toBeInstanceOf(ProjectData::class)
        ->and($result->gid)->toBe('789')
        ->and($result->name)->toBe('Test Project')
        ->and($result->archived)->toBeFalse();
});

test('list returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Project 1', 'resource_type' => 'project'],
                ['gid' => '2', 'name' => 'Project 2', 'resource_type' => 'project'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->list('ws1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(ProjectData::class)
        ->and($result->data[0]->name)->toBe('Project 1');
});

test('create returns ProjectData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '999',
            'name' => 'New Project',
            'resource_type' => 'project',
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->create(['name' => 'New Project', 'workspace' => 'ws1']);

    expect($result)->toBeInstanceOf(ProjectData::class)
        ->and($result->gid)->toBe('999')
        ->and($result->name)->toBe('New Project');
});

test('update returns ProjectData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '789',
            'name' => 'Updated Project',
            'resource_type' => 'project',
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->update('789', ['name' => 'Updated Project']);

    expect($result)->toBeInstanceOf(ProjectData::class)
        ->and($result->name)->toBe('Updated Project');
});

test('delete returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->delete('789');

    expect($result)->toBeTrue();
});

test('duplicate returns array data', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1000',
            'new_project' => ['gid' => '1001', 'name' => 'Copy of Project'],
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->duplicate('789', ['name' => 'Copy of Project']);

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('gid');
});

test('getTaskCounts returns array data', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'num_tasks' => 10,
            'num_incomplete_tasks' => 4,
            'num_completed_tasks' => 6,
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->getTaskCounts('789');

    expect($result)->toBeArray()
        ->and($result['num_tasks'])->toBe(10)
        ->and($result['num_incomplete_tasks'])->toBe(4);
});

test('getForTeam returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Team Project', 'resource_type' => 'project'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->getForTeam('team1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1)
        ->and($result->data[0]->name)->toBe('Team Project');
});

test('saveAsTemplate returns JobData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => 'job1',
            'resource_type' => 'job',
            'resource_subtype' => 'save_as_template',
            'status' => 'not_started',
            'new_project_template' => ['gid' => 'pt1', 'name' => 'Sprint', 'resource_type' => 'project_template'],
        ]], 201),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->saveAsTemplate('p1', ['name' => 'Sprint', 'team' => 'team1', 'public' => true]);

    expect($result)->toBeInstanceOf(JobData::class)
        ->and($result->new_project_template->gid)->toBe('pt1');

    $mockClient->assertSent(fn (Request $request) => $request instanceof SaveProjectAsTemplateRequest
        && $request->resolveEndpoint() === '/projects/p1/saveAsTemplate'
        && $request->body()->all() === ['data' => ['name' => 'Sprint', 'team' => 'team1', 'public' => true]]);
});

test('getMemberships returns PaginatedResponse of ProjectMembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'resource_type' => 'project_membership', 'access_level' => 'editor'],
                ['gid' => '2', 'resource_type' => 'project_membership', 'access_level' => 'viewer'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->getMemberships('proj1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(ProjectMembershipData::class)
        ->and($result->data[0]->access_level)->toBe('editor')
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/projects/proj1/project_memberships');
});

test('getMemberships sends user filter and pagination as query params', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => null], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $resource->getMemberships('proj1', 'user1', ['access_level'], 'abc', 25);

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === [
        'user' => 'user1',
        'opt_fields' => 'access_level',
        'offset' => 'abc',
        'limit' => 25,
    ]);
});

test('getMemberships handles pagination', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'resource_type' => 'project_membership']],
            'next_page' => ['offset' => 'tok', 'uri' => '/projects/proj1/project_memberships?offset=tok'],
        ], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->getMemberships('proj1');

    expect($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');
});

test('getMembership returns ProjectMembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '700',
            'resource_type' => 'project_membership',
            'access_level' => 'admin',
            'write_access' => 'full_write',
            'user' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        ]], 200),
    ]);

    $resource = createProjectResource($mockClient);
    $result = $resource->getMembership('700', ['write_access']);

    expect($result)->toBeInstanceOf(ProjectMembershipData::class)
        ->and($result->gid)->toBe('700')
        ->and($result->write_access)->toBe('full_write')
        ->and($result->user->name)->toBe('Jane');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/project_memberships/700'
        && $request->query()->all() === ['opt_fields' => 'write_access']);
});
