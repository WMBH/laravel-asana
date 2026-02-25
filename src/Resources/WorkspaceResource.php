<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\WorkspaceData;
use WMBH\Asana\Requests\Workspaces\AddUserToWorkspaceRequest;
use WMBH\Asana\Requests\Workspaces\GetWorkspaceRequest;
use WMBH\Asana\Requests\Workspaces\GetWorkspacesRequest;
use WMBH\Asana\Requests\Workspaces\RemoveUserFromWorkspaceRequest;
use WMBH\Asana\Requests\Workspaces\UpdateWorkspaceRequest;

class WorkspaceResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): WorkspaceData
    {
        $response = $this->connector->send(new GetWorkspaceRequest($gid, $optFields));

        return WorkspaceData::from($response->json('data'));
    }

    public function list(array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetWorkspacesRequest($optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), WorkspaceData::class);
    }

    public function update(string $gid, array $data): WorkspaceData
    {
        $response = $this->connector->send(new UpdateWorkspaceRequest($gid, $data));

        return WorkspaceData::from($response->json('data'));
    }

    public function addUser(string $workspaceGid, string $userGid): void
    {
        $this->connector->send(new AddUserToWorkspaceRequest($workspaceGid, $userGid));
    }

    public function removeUser(string $workspaceGid, string $userGid): void
    {
        $this->connector->send(new RemoveUserFromWorkspaceRequest($workspaceGid, $userGid));
    }
}
