<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\SectionData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Resources\SectionResource;

function createSectionResource(MockClient $mockClient): SectionResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new SectionResource($connector);
}

test('get returns SectionData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '100',
            'name' => 'To Do',
            'resource_type' => 'section',
        ]], 200),
    ]);

    $resource = createSectionResource($mockClient);
    $result = $resource->get('100');

    expect($result)->toBeInstanceOf(SectionData::class)
        ->and($result->gid)->toBe('100')
        ->and($result->name)->toBe('To Do');
});

test('getForProject returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'To Do', 'resource_type' => 'section'],
                ['gid' => '2', 'name' => 'In Progress', 'resource_type' => 'section'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createSectionResource($mockClient);
    $result = $resource->getForProject('proj1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(SectionData::class)
        ->and($result->data[0]->name)->toBe('To Do');
});

test('create returns SectionData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '200',
            'name' => 'New Section',
            'resource_type' => 'section',
        ]], 200),
    ]);

    $resource = createSectionResource($mockClient);
    $result = $resource->create('proj1', ['name' => 'New Section']);

    expect($result)->toBeInstanceOf(SectionData::class)
        ->and($result->gid)->toBe('200')
        ->and($result->name)->toBe('New Section');
});

test('update returns SectionData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '100',
            'name' => 'Renamed',
            'resource_type' => 'section',
        ]], 200),
    ]);

    $resource = createSectionResource($mockClient);
    $result = $resource->update('100', ['name' => 'Renamed']);

    expect($result)->toBeInstanceOf(SectionData::class)
        ->and($result->name)->toBe('Renamed');
});

test('delete returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createSectionResource($mockClient);

    expect($resource->delete('100'))->toBeTrue();
});

test('addTask sends request without error', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createSectionResource($mockClient);
    $resource->addTask('sec1', 'task1');

    $mockClient->assertSentCount(1);
});

test('insertSection sends request without error', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createSectionResource($mockClient);
    $resource->insertSection('proj1', ['section' => 'sec1', 'before_section' => 'sec2']);

    $mockClient->assertSentCount(1);
});
