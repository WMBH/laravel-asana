<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\TaskData;
use WMBH\Asana\Query\TaskQueryBuilder;
use WMBH\Asana\Requests\Tasks\AddDependenciesRequest;
use WMBH\Asana\Requests\Tasks\AddDependentsRequest;
use WMBH\Asana\Requests\Tasks\AddFollowersRequest;
use WMBH\Asana\Requests\Tasks\AddProjectToTaskRequest;
use WMBH\Asana\Requests\Tasks\AddTagToTaskRequest;
use WMBH\Asana\Requests\Tasks\CreateTaskRequest;
use WMBH\Asana\Requests\Tasks\DeleteTaskRequest;
use WMBH\Asana\Requests\Tasks\GetDependenciesRequest;
use WMBH\Asana\Requests\Tasks\GetDependentsRequest;
use WMBH\Asana\Requests\Tasks\GetSubtasksRequest;
use WMBH\Asana\Requests\Tasks\GetTaskRequest;
use WMBH\Asana\Requests\Tasks\GetTasksForProjectRequest;
use WMBH\Asana\Requests\Tasks\GetTasksForSectionRequest;
use WMBH\Asana\Requests\Tasks\RemoveProjectFromTaskRequest;
use WMBH\Asana\Requests\Tasks\RemoveTagFromTaskRequest;
use WMBH\Asana\Requests\Tasks\SearchTasksRequest;
use WMBH\Asana\Requests\Tasks\SetParentRequest;
use WMBH\Asana\Requests\Tasks\UpdateTaskRequest;

class TaskResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): TaskData
    {
        $response = $this->connector->send(new GetTaskRequest($gid, $optFields));

        return TaskData::from($response->json('data'));
    }

    public function getForProject(string $projectGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetTasksForProjectRequest($projectGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), TaskData::class);
    }

    public function getForSection(string $sectionGid, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetTasksForSectionRequest($sectionGid, $optFields));

        return PaginatedResponse::fromResponse($response->json(), TaskData::class);
    }

    public function getSubtasks(string $taskGid, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetSubtasksRequest($taskGid, $optFields));

        return PaginatedResponse::fromResponse($response->json(), TaskData::class);
    }

    public function create(array $data): TaskData
    {
        $response = $this->connector->send(new CreateTaskRequest($data));

        return TaskData::from($response->json('data'));
    }

    public function update(string $gid, array $data): TaskData
    {
        $response = $this->connector->send(new UpdateTaskRequest($gid, $data));

        return TaskData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $response = $this->connector->send(new DeleteTaskRequest($gid));

        return $response->status() === 200;
    }

    public function search(string $workspaceGid, array $params = []): TaskQueryBuilder|PaginatedResponse
    {
        if (empty($params)) {
            return new TaskQueryBuilder($this->connector, $workspaceGid);
        }

        $response = $this->connector->send(new SearchTasksRequest($workspaceGid, $params));

        return PaginatedResponse::fromResponse($response->json(), TaskData::class);
    }

    public function addTag(string $taskGid, string $tagGid): void
    {
        $this->connector->send(new AddTagToTaskRequest($taskGid, $tagGid));
    }

    public function removeTag(string $taskGid, string $tagGid): void
    {
        $this->connector->send(new RemoveTagFromTaskRequest($taskGid, $tagGid));
    }

    public function addFollowers(string $taskGid, array $followers): void
    {
        $this->connector->send(new AddFollowersRequest($taskGid, $followers));
    }

    public function addProject(string $taskGid, string $projectGid, ?string $sectionGid = null, ?string $insertBefore = null, ?string $insertAfter = null): void
    {
        $this->connector->send(new AddProjectToTaskRequest($taskGid, $projectGid, $sectionGid, $insertBefore, $insertAfter));
    }

    public function removeProject(string $taskGid, string $projectGid): void
    {
        $this->connector->send(new RemoveProjectFromTaskRequest($taskGid, $projectGid));
    }

    public function setParent(string $taskGid, string $parentGid): TaskData
    {
        $response = $this->connector->send(new SetParentRequest($taskGid, $parentGid));

        return TaskData::from($response->json('data'));
    }

    public function getDependencies(string $taskGid): PaginatedResponse
    {
        $response = $this->connector->send(new GetDependenciesRequest($taskGid));

        return PaginatedResponse::fromResponse($response->json(), TaskData::class);
    }

    public function getDependents(string $taskGid): PaginatedResponse
    {
        $response = $this->connector->send(new GetDependentsRequest($taskGid));

        return PaginatedResponse::fromResponse($response->json(), TaskData::class);
    }

    public function addDependencies(string $taskGid, array $dependencyGids): void
    {
        $this->connector->send(new AddDependenciesRequest($taskGid, $dependencyGids));
    }

    public function addDependents(string $taskGid, array $dependentGids): void
    {
        $this->connector->send(new AddDependentsRequest($taskGid, $dependentGids));
    }
}
