<?php

use WMBH\Asana\Asana;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Resources\AttachmentResource;
use WMBH\Asana\Resources\BatchResource;
use WMBH\Asana\Resources\CustomFieldResource;
use WMBH\Asana\Resources\GoalResource;
use WMBH\Asana\Resources\PortfolioResource;
use WMBH\Asana\Resources\ProjectResource;
use WMBH\Asana\Resources\SectionResource;
use WMBH\Asana\Resources\StoryResource;
use WMBH\Asana\Resources\TagResource;
use WMBH\Asana\Resources\TaskResource;
use WMBH\Asana\Resources\TeamResource;
use WMBH\Asana\Resources\UserResource;
use WMBH\Asana\Resources\WebhookResource;
use WMBH\Asana\Resources\WorkspaceResource;

test('Asana class returns resource instances', function () {
    $connector = new AsanaConnector('test-token');
    $asana = new Asana($connector);

    expect($asana->tasks())->toBeInstanceOf(TaskResource::class)
        ->and($asana->projects())->toBeInstanceOf(ProjectResource::class)
        ->and($asana->sections())->toBeInstanceOf(SectionResource::class)
        ->and($asana->workspaces())->toBeInstanceOf(WorkspaceResource::class)
        ->and($asana->users())->toBeInstanceOf(UserResource::class)
        ->and($asana->teams())->toBeInstanceOf(TeamResource::class)
        ->and($asana->tags())->toBeInstanceOf(TagResource::class)
        ->and($asana->stories())->toBeInstanceOf(StoryResource::class)
        ->and($asana->attachments())->toBeInstanceOf(AttachmentResource::class)
        ->and($asana->customFields())->toBeInstanceOf(CustomFieldResource::class)
        ->and($asana->portfolios())->toBeInstanceOf(PortfolioResource::class)
        ->and($asana->goals())->toBeInstanceOf(GoalResource::class)
        ->and($asana->webhooks())->toBeInstanceOf(WebhookResource::class)
        ->and($asana->batch())->toBeInstanceOf(BatchResource::class);
});

test('resource accessors return same instance (lazy loading)', function () {
    $connector = new AsanaConnector('test-token');
    $asana = new Asana($connector);

    expect($asana->tasks())->toBe($asana->tasks())
        ->and($asana->projects())->toBe($asana->projects())
        ->and($asana->users())->toBe($asana->users());
});

test('getConnector returns the connector', function () {
    $connector = new AsanaConnector('test-token');
    $asana = new Asana($connector);

    expect($asana->getConnector())->toBe($connector);
});

test('testConnection returns true on success', function () {
    $mockClient = new \Saloon\Http\Faking\MockClient([
        \Saloon\Http\Faking\MockResponse::make(['data' => ['gid' => '1', 'name' => 'Test User']], 200),
    ]);

    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);
    $asana = new Asana($connector);

    expect($asana->testConnection())->toBeTrue();
});

test('testConnection returns false on failure', function () {
    $mockClient = new \Saloon\Http\Faking\MockClient([
        \Saloon\Http\Faking\MockResponse::make(['errors' => [['message' => 'Not Authorized']]], 401),
    ]);

    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);
    $asana = new Asana($connector);

    expect($asana->testConnection())->toBeFalse();
});
