<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\ProjectBriefData;
use WMBH\Asana\Requests\ProjectBriefs\CreateProjectBriefRequest;
use WMBH\Asana\Requests\ProjectBriefs\DeleteProjectBriefRequest;
use WMBH\Asana\Requests\ProjectBriefs\GetProjectBriefRequest;
use WMBH\Asana\Requests\ProjectBriefs\UpdateProjectBriefRequest;

class ProjectBriefResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): ProjectBriefData
    {
        $response = $this->connector->send(new GetProjectBriefRequest($gid, $optFields));

        return ProjectBriefData::from($response->json('data'));
    }

    public function create(string $projectGid, array $data, array $optFields = []): ProjectBriefData
    {
        $response = $this->connector->send(new CreateProjectBriefRequest($projectGid, $data, $optFields));

        return ProjectBriefData::from($response->json('data'));
    }

    public function update(string $gid, array $data, array $optFields = []): ProjectBriefData
    {
        $response = $this->connector->send(new UpdateProjectBriefRequest($gid, $data, $optFields));

        return ProjectBriefData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $response = $this->connector->send(new DeleteProjectBriefRequest($gid));

        return $response->status() === 200;
    }
}
