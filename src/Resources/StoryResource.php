<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\StoryData;
use WMBH\Asana\Requests\Stories\CreateStoryRequest;
use WMBH\Asana\Requests\Stories\DeleteStoryRequest;
use WMBH\Asana\Requests\Stories\GetStoriesForTaskRequest;
use WMBH\Asana\Requests\Stories\GetStoryRequest;
use WMBH\Asana\Requests\Stories\UpdateStoryRequest;

class StoryResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): StoryData
    {
        $response = $this->connector->send(new GetStoryRequest($gid, $optFields));

        return StoryData::from($response->json('data'));
    }

    public function getForTask(string $taskGid, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetStoriesForTaskRequest($taskGid, $optFields));

        return PaginatedResponse::fromResponse($response->json(), StoryData::class);
    }

    public function create(string $taskGid, array $data): StoryData
    {
        $response = $this->connector->send(new CreateStoryRequest($taskGid, $data));

        return StoryData::from($response->json('data'));
    }

    public function update(string $gid, array $data): StoryData
    {
        $response = $this->connector->send(new UpdateStoryRequest($gid, $data));

        return StoryData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $this->connector->send(new DeleteStoryRequest($gid));

        return true;
    }
}
