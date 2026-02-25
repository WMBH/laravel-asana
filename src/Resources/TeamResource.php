<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\TeamData;
use WMBH\Asana\Requests\Teams\AddUserToTeamRequest;
use WMBH\Asana\Requests\Teams\CreateTeamRequest;
use WMBH\Asana\Requests\Teams\GetTeamRequest;
use WMBH\Asana\Requests\Teams\GetTeamsForUserRequest;
use WMBH\Asana\Requests\Teams\GetTeamsForWorkspaceRequest;
use WMBH\Asana\Requests\Teams\RemoveUserFromTeamRequest;

class TeamResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): TeamData
    {
        $response = $this->connector->send(new GetTeamRequest($gid, $optFields));

        return TeamData::from($response->json('data'));
    }

    public function getForWorkspace(string $workspaceGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetTeamsForWorkspaceRequest($workspaceGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), TeamData::class);
    }

    public function getForUser(string $userGid, string $organizationGid, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetTeamsForUserRequest($userGid, $organizationGid, $optFields));

        return PaginatedResponse::fromResponse($response->json(), TeamData::class);
    }

    public function create(array $data): TeamData
    {
        $response = $this->connector->send(new CreateTeamRequest($data));

        return TeamData::from($response->json('data'));
    }

    public function addUser(string $teamGid, string $userGid): void
    {
        $this->connector->send(new AddUserToTeamRequest($teamGid, $userGid));
    }

    public function removeUser(string $teamGid, string $userGid): void
    {
        $this->connector->send(new RemoveUserFromTeamRequest($teamGid, $userGid));
    }
}
