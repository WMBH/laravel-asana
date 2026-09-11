<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\MembershipData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Resources\MembershipResource;

function createMembershipResource(MockClient $mockClient): MembershipResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new MembershipResource($connector);
}

test('list returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'resource_type' => 'project_membership', 'access_level' => 'editor'],
                ['gid' => '2', 'resource_type' => 'project_membership', 'access_level' => 'viewer'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createMembershipResource($mockClient);
    $result = $resource->list('proj1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(MembershipData::class)
        ->and($result->data[0]->access_level)->toBe('editor')
        ->and($result->hasNextPage())->toBeFalse();
});

test('list sends filters as query params', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => null], 200),
    ]);

    $resource = createMembershipResource($mockClient);
    $resource->list('proj1', 'user1', 'project_membership', ['access_level'], 'abc', 50);

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/memberships'
        && $request->query()->all() === [
            'parent' => 'proj1',
            'member' => 'user1',
            'resource_subtype' => 'project_membership',
            'opt_fields' => 'access_level',
            'offset' => 'abc',
            'limit' => 50,
        ]);
});

test('list omits null filters', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [], 'next_page' => null], 200),
    ]);

    $resource = createMembershipResource($mockClient);
    $resource->list();

    $mockClient->assertSent(fn (Request $request) => $request->query()->all() === []);
});

test('list handles pagination', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [['gid' => '1', 'resource_type' => 'project_membership']],
            'next_page' => ['offset' => 'tok', 'uri' => '/memberships?offset=tok'],
        ], 200),
    ]);

    $resource = createMembershipResource($mockClient);
    $result = $resource->list('proj1');

    expect($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');
});

test('get returns MembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '900',
            'resource_type' => 'project_membership',
            'access_level' => 'admin',
            'member' => ['gid' => '2', 'name' => 'Jane', 'resource_type' => 'user'],
        ]], 200),
    ]);

    $resource = createMembershipResource($mockClient);
    $result = $resource->get('900');

    expect($result)->toBeInstanceOf(MembershipData::class)
        ->and($result->gid)->toBe('900')
        ->and($result->access_level)->toBe('admin')
        ->and($result->member->name)->toBe('Jane');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/memberships/900');
});

test('create returns MembershipData and wraps body in data envelope', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '901',
            'resource_type' => 'project_membership',
            'access_level' => 'editor',
        ]], 201),
    ]);

    $resource = createMembershipResource($mockClient);
    $result = $resource->create(['parent' => 'proj1', 'member' => 'user1', 'access_level' => 'editor']);

    expect($result)->toBeInstanceOf(MembershipData::class)
        ->and($result->gid)->toBe('901');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/memberships'
        && $request->body()->all() === ['data' => ['parent' => 'proj1', 'member' => 'user1', 'access_level' => 'editor']]);
});

test('update returns MembershipData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '900',
            'resource_type' => 'project_membership',
            'access_level' => 'viewer',
        ]], 200),
    ]);

    $resource = createMembershipResource($mockClient);
    $result = $resource->update('900', ['access_level' => 'viewer']);

    expect($result)->toBeInstanceOf(MembershipData::class)
        ->and($result->access_level)->toBe('viewer');

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/memberships/900'
        && $request->body()->all() === ['data' => ['access_level' => 'viewer']]);
});

test('delete returns true on 200', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createMembershipResource($mockClient);

    expect($resource->delete('900'))->toBeTrue();

    $mockClient->assertSent(fn (Request $request) => $request->resolveEndpoint() === '/memberships/900');
});
