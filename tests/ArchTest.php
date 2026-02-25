<?php

// ── General ──────────────────────────────────────────────────────────

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

// ── Connector (Lawman) ──────────────────────────────────────────────

arch('connector is a valid Saloon connector')
    ->expect('WMBH\Asana\AsanaConnector')
    ->toBeSaloonConnector()
    ->toUseTokenAuthentication()
    ->toUseAlwaysThrowOnErrorsTrait();

// ── Requests (Lawman) ───────────────────────────────────────────────

arch('all requests are valid Saloon requests')
    ->expect('WMBH\Asana\Requests')
    ->toBeSaloonRequest();

arch('GetTaskRequest sends GET')
    ->expect('WMBH\Asana\Requests\Tasks\GetTaskRequest')
    ->toSendGetRequest();

arch('GetProjectRequest sends GET')
    ->expect('WMBH\Asana\Requests\Projects\GetProjectRequest')
    ->toSendGetRequest();

arch('GetMeRequest sends GET')
    ->expect('WMBH\Asana\Requests\Users\GetMeRequest')
    ->toSendGetRequest();

arch('SearchTasksRequest sends GET')
    ->expect('WMBH\Asana\Requests\Tasks\SearchTasksRequest')
    ->toSendGetRequest();

arch('GetWebhooksForWorkspaceRequest sends GET')
    ->expect('WMBH\Asana\Requests\Webhooks\GetWebhooksForWorkspaceRequest')
    ->toSendGetRequest();

arch('CreateTaskRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Tasks\CreateTaskRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('CreateProjectRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Projects\CreateProjectRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('AddTagToTaskRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Tasks\AddTagToTaskRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('CreateWebhookRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Webhooks\CreateWebhookRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('SubmitBatchRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Batch\SubmitBatchRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('UpdateTaskRequest sends PUT with JSON body')
    ->expect('WMBH\Asana\Requests\Tasks\UpdateTaskRequest')
    ->toSendPutRequest()
    ->toHaveJsonBody();

arch('UpdateProjectRequest sends PUT with JSON body')
    ->expect('WMBH\Asana\Requests\Projects\UpdateProjectRequest')
    ->toSendPutRequest()
    ->toHaveJsonBody();

arch('UpdateWebhookRequest sends PUT with JSON body')
    ->expect('WMBH\Asana\Requests\Webhooks\UpdateWebhookRequest')
    ->toSendPutRequest()
    ->toHaveJsonBody();

arch('DeleteTaskRequest sends DELETE')
    ->expect('WMBH\Asana\Requests\Tasks\DeleteTaskRequest')
    ->toSendDeleteRequest();

arch('DeleteProjectRequest sends DELETE')
    ->expect('WMBH\Asana\Requests\Projects\DeleteProjectRequest')
    ->toSendDeleteRequest();

arch('DeleteWebhookRequest sends DELETE')
    ->expect('WMBH\Asana\Requests\Webhooks\DeleteWebhookRequest')
    ->toSendDeleteRequest();

// ── Exceptions ──────────────────────────────────────────────────────

arch('all exceptions extend AsanaException')
    ->expect('WMBH\Asana\Exceptions')
    ->toExtend('WMBH\Asana\Exceptions\AsanaException')
    ->ignoring('WMBH\Asana\Exceptions\AsanaException');

// ── Resources ───────────────────────────────────────────────────────

arch('all resources have Resource suffix')
    ->expect('WMBH\Asana\Resources')
    ->toHaveSuffix('Resource');

// ── DTOs ────────────────────────────────────────────────────────────

arch('all DTOs extend Data')
    ->expect('WMBH\Asana\Data')
    ->toExtend('Spatie\LaravelData\Data')
    ->ignoring([
        'WMBH\Asana\Data\Shared\PaginatedResponse',
        'WMBH\Asana\Data\Shared\ErrorResponse',
    ]);
