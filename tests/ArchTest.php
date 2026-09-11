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

arch('GetAttachmentsForObjectRequest sends GET')
    ->expect('WMBH\Asana\Requests\Attachments\GetAttachmentsForObjectRequest')
    ->toSendGetRequest();

arch('GetTaskTemplateRequest sends GET')
    ->expect('WMBH\Asana\Requests\TaskTemplates\GetTaskTemplateRequest')
    ->toSendGetRequest();

arch('InstantiateTaskRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\TaskTemplates\InstantiateTaskRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('DeleteTaskTemplateRequest sends DELETE')
    ->expect('WMBH\Asana\Requests\TaskTemplates\DeleteTaskTemplateRequest')
    ->toSendDeleteRequest();

arch('GetProjectTemplateRequest sends GET')
    ->expect('WMBH\Asana\Requests\ProjectTemplates\GetProjectTemplateRequest')
    ->toSendGetRequest();

arch('InstantiateProjectRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\ProjectTemplates\InstantiateProjectRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('SaveProjectAsTemplateRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Projects\SaveProjectAsTemplateRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('DeleteProjectTemplateRequest sends DELETE')
    ->expect('WMBH\Asana\Requests\ProjectTemplates\DeleteProjectTemplateRequest')
    ->toSendDeleteRequest();

arch('GetJobRequest sends GET')
    ->expect('WMBH\Asana\Requests\Jobs\GetJobRequest')
    ->toSendGetRequest();

arch('GetTasksRequest sends GET')
    ->expect('WMBH\Asana\Requests\Tasks\GetTasksRequest')
    ->toSendGetRequest();

arch('GetTaskByCustomIdRequest sends GET')
    ->expect('WMBH\Asana\Requests\Tasks\GetTaskByCustomIdRequest')
    ->toSendGetRequest();

arch('DuplicateTaskRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Tasks\DuplicateTaskRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('CreateSubtaskRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Tasks\CreateSubtaskRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('RemoveFollowersRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Tasks\RemoveFollowersRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('GetTagsRequest sends GET')
    ->expect('WMBH\Asana\Requests\Tags\GetTagsRequest')
    ->toSendGetRequest();

arch('GetStatusUpdateRequest sends GET')
    ->expect('WMBH\Asana\Requests\StatusUpdates\GetStatusUpdateRequest')
    ->toSendGetRequest();

arch('CreateStatusUpdateRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\StatusUpdates\CreateStatusUpdateRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('DeleteStatusUpdateRequest sends DELETE')
    ->expect('WMBH\Asana\Requests\StatusUpdates\DeleteStatusUpdateRequest')
    ->toSendDeleteRequest();

arch('GetProjectBriefRequest sends GET')
    ->expect('WMBH\Asana\Requests\ProjectBriefs\GetProjectBriefRequest')
    ->toSendGetRequest();

arch('CreateProjectBriefRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\ProjectBriefs\CreateProjectBriefRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('UpdateProjectBriefRequest sends PUT with JSON body')
    ->expect('WMBH\Asana\Requests\ProjectBriefs\UpdateProjectBriefRequest')
    ->toSendPutRequest()
    ->toHaveJsonBody();

arch('DeleteProjectBriefRequest sends DELETE')
    ->expect('WMBH\Asana\Requests\ProjectBriefs\DeleteProjectBriefRequest')
    ->toSendDeleteRequest();

arch('GetEventsRequest sends GET')
    ->expect('WMBH\Asana\Requests\Events\GetEventsRequest')
    ->toSendGetRequest();

arch('GetWorkspaceEventsRequest sends GET')
    ->expect('WMBH\Asana\Requests\Events\GetWorkspaceEventsRequest')
    ->toSendGetRequest();

arch('GetCustomTypesRequest sends GET')
    ->expect('WMBH\Asana\Requests\CustomTypes\GetCustomTypesRequest')
    ->toSendGetRequest();

arch('GetCustomTypeRequest sends GET')
    ->expect('WMBH\Asana\Requests\CustomTypes\GetCustomTypeRequest')
    ->toSendGetRequest();

arch('GetUserTaskListRequest sends GET')
    ->expect('WMBH\Asana\Requests\UserTaskLists\GetUserTaskListRequest')
    ->toSendGetRequest();

arch('GetUserTaskListForUserRequest sends GET')
    ->expect('WMBH\Asana\Requests\UserTaskLists\GetUserTaskListForUserRequest')
    ->toSendGetRequest();

arch('GetAccessRequestsRequest sends GET')
    ->expect('WMBH\Asana\Requests\AccessRequests\GetAccessRequestsRequest')
    ->toSendGetRequest();

arch('CreateAccessRequestRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\AccessRequests\CreateAccessRequestRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('ApproveAccessRequestRequest sends POST')
    ->expect('WMBH\Asana\Requests\AccessRequests\ApproveAccessRequestRequest')
    ->toSendPostRequest();

arch('RejectAccessRequestRequest sends POST')
    ->expect('WMBH\Asana\Requests\AccessRequests\RejectAccessRequestRequest')
    ->toSendPostRequest();

arch('GetReactionsForObjectRequest sends GET')
    ->expect('WMBH\Asana\Requests\Reactions\GetReactionsForObjectRequest')
    ->toSendGetRequest();

arch('GetMembershipsRequest sends GET')
    ->expect('WMBH\Asana\Requests\Memberships\GetMembershipsRequest')
    ->toSendGetRequest();

arch('CreateMembershipRequest sends POST with JSON body')
    ->expect('WMBH\Asana\Requests\Memberships\CreateMembershipRequest')
    ->toSendPostRequest()
    ->toHaveJsonBody();

arch('UpdateMembershipRequest sends PUT with JSON body')
    ->expect('WMBH\Asana\Requests\Memberships\UpdateMembershipRequest')
    ->toSendPutRequest()
    ->toHaveJsonBody();

arch('DeleteMembershipRequest sends DELETE')
    ->expect('WMBH\Asana\Requests\Memberships\DeleteMembershipRequest')
    ->toSendDeleteRequest();

arch('GetProjectMembershipsRequest sends GET')
    ->expect('WMBH\Asana\Requests\Projects\GetProjectMembershipsRequest')
    ->toSendGetRequest();

arch('GetProjectMembershipRequest sends GET')
    ->expect('WMBH\Asana\Requests\Projects\GetProjectMembershipRequest')
    ->toSendGetRequest();

arch('UpdateTeamRequest sends PUT with JSON body')
    ->expect('WMBH\Asana\Requests\Teams\UpdateTeamRequest')
    ->toSendPutRequest()
    ->toHaveJsonBody();

arch('GetTeamMembershipsRequest sends GET')
    ->expect('WMBH\Asana\Requests\Teams\GetTeamMembershipsRequest')
    ->toSendGetRequest();

arch('GetTeamMembershipRequest sends GET')
    ->expect('WMBH\Asana\Requests\Teams\GetTeamMembershipRequest')
    ->toSendGetRequest();

arch('GetWorkspaceMembershipsRequest sends GET')
    ->expect('WMBH\Asana\Requests\Workspaces\GetWorkspaceMembershipsRequest')
    ->toSendGetRequest();

arch('GetWorkspaceMembershipRequest sends GET')
    ->expect('WMBH\Asana\Requests\Workspaces\GetWorkspaceMembershipRequest')
    ->toSendGetRequest();

arch('TypeaheadRequest sends GET')
    ->expect('WMBH\Asana\Requests\Workspaces\TypeaheadRequest')
    ->toSendGetRequest();

arch('UpdateUserRequest sends PUT with JSON body')
    ->expect('WMBH\Asana\Requests\Users\UpdateUserRequest')
    ->toSendPutRequest()
    ->toHaveJsonBody();

arch('UpdateUserForWorkspaceRequest sends PUT with JSON body')
    ->expect('WMBH\Asana\Requests\Users\UpdateUserForWorkspaceRequest')
    ->toSendPutRequest()
    ->toHaveJsonBody();

arch('GetFavoritesForUserRequest sends GET')
    ->expect('WMBH\Asana\Requests\Users\GetFavoritesForUserRequest')
    ->toSendGetRequest();

arch('GetUserForWorkspaceRequest sends GET')
    ->expect('WMBH\Asana\Requests\Users\GetUserForWorkspaceRequest')
    ->toSendGetRequest();

arch('GetTeamMembershipsForUserRequest sends GET')
    ->expect('WMBH\Asana\Requests\Teams\GetTeamMembershipsForUserRequest')
    ->toSendGetRequest();

arch('GetWorkspaceMembershipsForUserRequest sends GET')
    ->expect('WMBH\Asana\Requests\Workspaces\GetWorkspaceMembershipsForUserRequest')
    ->toSendGetRequest();

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
        'WMBH\Asana\Data\Shared\EventsResponse',
    ]);
