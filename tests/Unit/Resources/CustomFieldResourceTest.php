<?php

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\CustomFieldData;
use WMBH\Asana\Data\CustomFieldSettingData;
use WMBH\Asana\Data\EnumOptionData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\CustomFields\GetCustomFieldSettingsForTeamRequest;
use WMBH\Asana\Resources\CustomFieldResource;

function createCustomFieldResource(MockClient $mockClient): CustomFieldResource
{
    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    return new CustomFieldResource($connector);
}

test('get returns CustomFieldData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '900',
            'name' => 'Priority',
            'resource_type' => 'custom_field',
            'resource_subtype' => 'enum',
            'type' => 'enum',
        ]], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->get('900');

    expect($result)->toBeInstanceOf(CustomFieldData::class)
        ->and($result->gid)->toBe('900')
        ->and($result->name)->toBe('Priority')
        ->and($result->resource_subtype)->toBe('enum');
});

test('getForWorkspace returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'name' => 'Priority', 'resource_type' => 'custom_field'],
                ['gid' => '2', 'name' => 'Points', 'resource_type' => 'custom_field'],
            ],
            'next_page' => null,
        ], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->getForWorkspace('ws1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(CustomFieldData::class);
});

test('create returns CustomFieldData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '901',
            'name' => 'Story Points',
            'resource_type' => 'custom_field',
            'resource_subtype' => 'number',
        ]], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->create([
        'name' => 'Story Points',
        'resource_subtype' => 'number',
        'workspace' => 'ws1',
    ]);

    expect($result)->toBeInstanceOf(CustomFieldData::class)
        ->and($result->gid)->toBe('901');
});

test('update returns CustomFieldData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '900',
            'name' => 'Updated Field',
            'resource_type' => 'custom_field',
        ]], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->update('900', ['name' => 'Updated Field']);

    expect($result)->toBeInstanceOf(CustomFieldData::class)
        ->and($result->name)->toBe('Updated Field');
});

test('delete returns true on success', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => []], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);

    expect($resource->delete('900'))->toBeTrue();
});

test('getSettingsForProject returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                [
                    'gid' => '1',
                    'resource_type' => 'custom_field_setting',
                    'is_important' => true,
                    'custom_field' => ['gid' => '900', 'name' => 'Priority'],
                ],
            ],
            'next_page' => ['offset' => 'tok', 'uri' => '/projects/p1/custom_field_settings?offset=tok'],
        ], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->getSettingsForProject('p1', ['is_important'], null, 50);

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(1)
        ->and($result->data[0])->toBeInstanceOf(CustomFieldSettingData::class)
        ->and($result->data[0]->is_important)->toBeTrue()
        ->and($result->hasNextPage())->toBeTrue()
        ->and($result->nextPageToken)->toBe('tok');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/projects/p1/custom_field_settings'
            && $request->query()->all() === ['opt_fields' => 'is_important', 'limit' => 50];
    });
});

test('getSettingsForTeam returns PaginatedResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make([
            'data' => [
                ['gid' => '1', 'resource_type' => 'custom_field_setting'],
                ['gid' => '2', 'resource_type' => 'custom_field_setting'],
            ],
        ], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->getSettingsForTeam('team1');

    expect($result)->toBeInstanceOf(PaginatedResponse::class)
        ->and($result->data)->toHaveCount(2)
        ->and($result->data[0])->toBeInstanceOf(CustomFieldSettingData::class)
        ->and($result->hasNextPage())->toBeFalse();

    $mockClient->assertSent(GetCustomFieldSettingsForTeamRequest::class);
});

test('createEnumOption returns EnumOptionData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '11',
            'resource_type' => 'enum_option',
            'name' => 'Urgent',
            'enabled' => true,
            'color' => 'red',
        ]], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->createEnumOption('900', ['name' => 'Urgent', 'color' => 'red']);

    expect($result)->toBeInstanceOf(EnumOptionData::class)
        ->and($result->gid)->toBe('11')
        ->and($result->name)->toBe('Urgent')
        ->and($result->color)->toBe('red');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/custom_fields/900/enum_options'
            && $request->body()->all() === ['data' => ['name' => 'Urgent', 'color' => 'red']];
    });
});

test('insertEnumOption returns EnumOptionData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '11',
            'resource_type' => 'enum_option',
            'name' => 'Urgent',
        ]], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->insertEnumOption('900', ['enum_option' => '11', 'before_enum_option' => '12']);

    expect($result)->toBeInstanceOf(EnumOptionData::class)
        ->and($result->gid)->toBe('11');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/custom_fields/900/enum_options/insert'
            && $request->body()->all() === ['data' => ['enum_option' => '11', 'before_enum_option' => '12']];
    });
});

test('updateEnumOption returns EnumOptionData', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => [
            'gid' => '11',
            'resource_type' => 'enum_option',
            'name' => 'Critical',
        ]], 200),
    ]);

    $resource = createCustomFieldResource($mockClient);
    $result = $resource->updateEnumOption('11', ['name' => 'Critical']);

    expect($result)->toBeInstanceOf(EnumOptionData::class)
        ->and($result->name)->toBe('Critical');

    $mockClient->assertSent(function ($request) {
        return $request->resolveEndpoint() === '/enum_options/11'
            && $request->body()->all() === ['data' => ['name' => 'Critical']];
    });
});
