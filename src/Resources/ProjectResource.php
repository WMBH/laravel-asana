<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\CustomFieldSettingData;
use WMBH\Asana\Data\JobData;
use WMBH\Asana\Data\ProjectData;
use WMBH\Asana\Data\ProjectMembershipData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\Projects\AddCustomFieldSettingToProjectRequest;
use WMBH\Asana\Requests\Projects\CreateProjectRequest;
use WMBH\Asana\Requests\Projects\DeleteProjectRequest;
use WMBH\Asana\Requests\Projects\DuplicateProjectRequest;
use WMBH\Asana\Requests\Projects\GetProjectMembershipRequest;
use WMBH\Asana\Requests\Projects\GetProjectMembershipsRequest;
use WMBH\Asana\Requests\Projects\GetProjectRequest;
use WMBH\Asana\Requests\Projects\GetProjectsForTeamRequest;
use WMBH\Asana\Requests\Projects\GetProjectsRequest;
use WMBH\Asana\Requests\Projects\GetTaskCountsRequest;
use WMBH\Asana\Requests\Projects\RemoveCustomFieldSettingFromProjectRequest;
use WMBH\Asana\Requests\Projects\SaveProjectAsTemplateRequest;
use WMBH\Asana\Requests\Projects\UpdateProjectRequest;

class ProjectResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): ProjectData
    {
        $response = $this->connector->send(new GetProjectRequest($gid, $optFields));

        return ProjectData::from($response->json('data'));
    }

    public function list(string $workspaceGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetProjectsRequest($workspaceGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), ProjectData::class);
    }

    public function getForTeam(string $teamGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetProjectsForTeamRequest($teamGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), ProjectData::class);
    }

    public function create(array $data): ProjectData
    {
        $response = $this->connector->send(new CreateProjectRequest($data));

        return ProjectData::from($response->json('data'));
    }

    public function update(string $gid, array $data): ProjectData
    {
        $response = $this->connector->send(new UpdateProjectRequest($gid, $data));

        return ProjectData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $response = $this->connector->send(new DeleteProjectRequest($gid));

        return $response->status() === 200;
    }

    public function duplicate(string $gid, array $data): array
    {
        $response = $this->connector->send(new DuplicateProjectRequest($gid, $data));

        return $response->json('data');
    }

    public function getTaskCounts(string $gid): array
    {
        $response = $this->connector->send(new GetTaskCountsRequest($gid));

        return $response->json('data');
    }

    public function addCustomFieldSetting(string $gid, array $data, array $optFields = []): CustomFieldSettingData
    {
        $response = $this->connector->send(new AddCustomFieldSettingToProjectRequest($gid, $data, $optFields));

        return CustomFieldSettingData::from($response->json('data'));
    }

    public function removeCustomFieldSetting(string $gid, string $customFieldGid): void
    {
        $this->connector->send(new RemoveCustomFieldSettingFromProjectRequest($gid, $customFieldGid));
    }

    public function getMemberships(string $gid, ?string $userGid = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetProjectMembershipsRequest($gid, $userGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), ProjectMembershipData::class);
    }

    public function getMembership(string $membershipGid, array $optFields = []): ProjectMembershipData
    {
        $response = $this->connector->send(new GetProjectMembershipRequest($membershipGid, $optFields));

        return ProjectMembershipData::from($response->json('data'));
    }

    public function saveAsTemplate(string $gid, array $data, array $optFields = []): JobData
    {
        $response = $this->connector->send(new SaveProjectAsTemplateRequest($gid, $data, $optFields));

        return JobData::from($response->json('data'));
    }
}
