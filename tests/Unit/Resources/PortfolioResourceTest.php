<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\PortfolioData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Resources\PortfolioResource;

function createPortfolioResource(MockClient $mockClient): PortfolioResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new PortfolioResource($connector);
}

test('get returns PortfolioData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1000',
            'name' => 'Q1 Projects',
            'resource_type' => 'portfolio',
            'color' => 'light-green',
        ]], 200),
    ]);

    $resource = createPortfolioResource($mockClient);
    $result = $resource->get('1000');

    expect($result)->toBeInstanceOf(PortfolioData::class)
        ->and($result->gid)->toBe('1000')
        ->and($result->name)->toBe('Q1 Projects')
        ->and($result->color)->toBe('light-green');
});

test('list returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Portfolio A', 'resource_type' => 'portfolio'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createPortfolioResource($mockClient);
    $result = $resource->list('ws1', 'owner1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1)
        ->and($result->data[0])->toBeInstanceOf(PortfolioData::class);
});

test('getItems returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '10', 'name' => 'Project X', 'resource_type' => 'project'],
                ['gid' => '11', 'name' => 'Project Y', 'resource_type' => 'project'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createPortfolioResource($mockClient);
    $result = $resource->getItems('1000');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2);
});

test('create returns PortfolioData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1001',
            'name' => 'New Portfolio',
            'resource_type' => 'portfolio',
        ]], 200),
    ]);

    $resource = createPortfolioResource($mockClient);
    $result = $resource->create(['name' => 'New Portfolio', 'workspace' => 'ws1']);

    expect($result)->toBeInstanceOf(PortfolioData::class)
        ->and($result->gid)->toBe('1001');
});

test('update returns PortfolioData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '1000',
            'name' => 'Updated Portfolio',
            'resource_type' => 'portfolio',
        ]], 200),
    ]);

    $resource = createPortfolioResource($mockClient);
    $result = $resource->update('1000', ['name' => 'Updated Portfolio']);

    expect($result)->toBeInstanceOf(PortfolioData::class)
        ->and($result->name)->toBe('Updated Portfolio');
});

test('addItem returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createPortfolioResource($mockClient);

    expect($resource->addItem('1000', 'project1'))->toBeTrue();
});

test('removeItem returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createPortfolioResource($mockClient);

    expect($resource->removeItem('1000', 'project1'))->toBeTrue();
});

test('delete returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createPortfolioResource($mockClient);

    expect($resource->delete('1000'))->toBeTrue();
});
