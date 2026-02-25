<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\UserData;
use WMBH\Asana\Requests\Users\GetMeRequest;
use WMBH\Asana\Requests\Users\GetUserRequest;
use WMBH\Asana\Requests\Users\GetUsersForTeamRequest;
use WMBH\Asana\Requests\Users\GetUsersForWorkspaceRequest;
use WMBH\Asana\Requests\Users\GetUsersRequest;

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
}
