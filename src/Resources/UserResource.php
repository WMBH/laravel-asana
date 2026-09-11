<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\TeamMembershipData;
use WMBH\Asana\Data\UserData;
use WMBH\Asana\Data\WorkspaceMembershipData;
use WMBH\Asana\Requests\Teams\GetTeamMembershipsForUserRequest;
use WMBH\Asana\Requests\Users\GetFavoritesForUserRequest;
use WMBH\Asana\Requests\Users\GetMeRequest;
use WMBH\Asana\Requests\Users\GetUserForWorkspaceRequest;
use WMBH\Asana\Requests\Users\GetUserRequest;
use WMBH\Asana\Requests\Users\GetUsersForTeamRequest;
use WMBH\Asana\Requests\Users\GetUsersForWorkspaceRequest;
use WMBH\Asana\Requests\Users\GetUsersRequest;
use WMBH\Asana\Requests\Users\UpdateUserForWorkspaceRequest;
use WMBH\Asana\Requests\Users\UpdateUserRequest;
use WMBH\Asana\Requests\Workspaces\GetWorkspaceMembershipsForUserRequest;

class UserResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): UserData
    {
        $response = $this->connector->send(new GetUserRequest($gid, $optFields));

        return UserData::from($response->json('data'));
    }

    public function list(array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetUsersRequest($optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), UserData::class);
    }

    public function getForWorkspace(string $workspaceGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetUsersForWorkspaceRequest($workspaceGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), UserData::class);
    }

    public function getForTeam(string $teamGid, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetUsersForTeamRequest($teamGid, $optFields));

        return PaginatedResponse::fromResponse($response->json(), UserData::class);
    }

    public function me(array $optFields = []): UserData
    {
        $response = $this->connector->send(new GetMeRequest($optFields));

        return UserData::from($response->json('data'));
    }

    public function update(string $gid, array $data, ?string $workspaceGid = null, array $optFields = []): UserData
    {
        $response = $this->connector->send(new UpdateUserRequest($gid, $data, $workspaceGid, $optFields));

        return UserData::from($response->json('data'));
    }

    public function getFavorites(string $userGid, string $resourceType, string $workspaceGid, ?string $offset = null, ?int $limit = null, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetFavoritesForUserRequest($userGid, $resourceType, $workspaceGid, $offset, $limit, $optFields));

        return PaginatedResponse::fromResponse($response->json(), CompactResource::class);
    }

    public function getInWorkspace(string $workspaceGid, string $userGid, array $optFields = []): UserData
    {
        $response = $this->connector->send(new GetUserForWorkspaceRequest($workspaceGid, $userGid, $optFields));

        return UserData::from($response->json('data'));
    }

    public function updateInWorkspace(string $workspaceGid, string $userGid, array $data, array $optFields = []): UserData
    {
        $response = $this->connector->send(new UpdateUserForWorkspaceRequest($workspaceGid, $userGid, $data, $optFields));

        return UserData::from($response->json('data'));
    }

    public function getTeamMemberships(string $userGid, string $workspaceGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetTeamMembershipsForUserRequest($userGid, $workspaceGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), TeamMembershipData::class);
    }

    public function getWorkspaceMemberships(string $userGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetWorkspaceMembershipsForUserRequest($userGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), WorkspaceMembershipData::class);
    }
}
