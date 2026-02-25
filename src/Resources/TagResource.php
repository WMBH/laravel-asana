<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\TagData;
use WMBH\Asana\Requests\Tags\CreateTagForWorkspaceRequest;
use WMBH\Asana\Requests\Tags\CreateTagRequest;
use WMBH\Asana\Requests\Tags\DeleteTagRequest;
use WMBH\Asana\Requests\Tags\GetTagRequest;
use WMBH\Asana\Requests\Tags\GetTagsForTaskRequest;
use WMBH\Asana\Requests\Tags\GetTagsForWorkspaceRequest;
use WMBH\Asana\Requests\Tags\UpdateTagRequest;

class TagResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): TagData
    {
        $response = $this->connector->send(new GetTagRequest($gid, $optFields));

        return TagData::from($response->json('data'));
    }

    public function getForTask(string $taskGid, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetTagsForTaskRequest($taskGid, $optFields));

        return PaginatedResponse::fromResponse($response->json(), TagData::class);
    }

    public function getForWorkspace(string $workspaceGid, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetTagsForWorkspaceRequest($workspaceGid, $optFields));

        return PaginatedResponse::fromResponse($response->json(), TagData::class);
    }

    public function create(array $data): TagData
    {
        $response = $this->connector->send(new CreateTagRequest($data));

        return TagData::from($response->json('data'));
    }

    public function createForWorkspace(string $workspaceGid, array $data): TagData
    {
        $response = $this->connector->send(new CreateTagForWorkspaceRequest($workspaceGid, $data));

        return TagData::from($response->json('data'));
    }

    public function update(string $gid, array $data): TagData
    {
        $response = $this->connector->send(new UpdateTagRequest($gid, $data));

        return TagData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $this->connector->send(new DeleteTagRequest($gid));

        return true;
    }
}
