<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\JobData;
use WMBH\Asana\Data\ProjectTemplateData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\ProjectTemplates\DeleteProjectTemplateRequest;
use WMBH\Asana\Requests\ProjectTemplates\GetProjectTemplateRequest;
use WMBH\Asana\Requests\ProjectTemplates\GetProjectTemplatesForTeamRequest;
use WMBH\Asana\Requests\ProjectTemplates\GetProjectTemplatesRequest;
use WMBH\Asana\Requests\ProjectTemplates\InstantiateProjectRequest;

class ProjectTemplateResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function list(?string $workspaceGid = null, ?string $teamGid = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetProjectTemplatesRequest($workspaceGid, $teamGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), ProjectTemplateData::class);
    }

    public function getForTeam(string $teamGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetProjectTemplatesForTeamRequest($teamGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), ProjectTemplateData::class);
    }

    public function get(string $gid, array $optFields = []): ProjectTemplateData
    {
        $response = $this->connector->send(new GetProjectTemplateRequest($gid, $optFields));

        return ProjectTemplateData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $response = $this->connector->send(new DeleteProjectTemplateRequest($gid));

        return $response->status() === 200;
    }

    public function instantiate(string $gid, array $data, array $optFields = []): JobData
    {
        $response = $this->connector->send(new InstantiateProjectRequest($gid, $data, $optFields));

        return JobData::from($response->json('data'));
    }
}
