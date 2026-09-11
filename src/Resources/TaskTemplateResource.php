<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\JobData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\TaskTemplateData;
use WMBH\Asana\Requests\TaskTemplates\DeleteTaskTemplateRequest;
use WMBH\Asana\Requests\TaskTemplates\GetTaskTemplateRequest;
use WMBH\Asana\Requests\TaskTemplates\GetTaskTemplatesRequest;
use WMBH\Asana\Requests\TaskTemplates\InstantiateTaskRequest;

class TaskTemplateResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function list(string $projectGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetTaskTemplatesRequest($projectGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), TaskTemplateData::class);
    }

    public function get(string $gid, array $optFields = []): TaskTemplateData
    {
        $response = $this->connector->send(new GetTaskTemplateRequest($gid, $optFields));

        return TaskTemplateData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $response = $this->connector->send(new DeleteTaskTemplateRequest($gid));

        return $response->status() === 200;
    }

    public function instantiate(string $gid, ?string $name = null, array $optFields = []): JobData
    {
        $response = $this->connector->send(new InstantiateTaskRequest($gid, $name, $optFields));

        return JobData::from($response->json('data'));
    }
}
