<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\GoalData;
use WMBH\Asana\Data\Shared\CompactResource;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\Goals\AddSubgoalRequest;
use WMBH\Asana\Requests\Goals\CreateGoalRequest;
use WMBH\Asana\Requests\Goals\DeleteGoalRequest;
use WMBH\Asana\Requests\Goals\GetGoalRelationshipsRequest;
use WMBH\Asana\Requests\Goals\GetGoalRequest;
use WMBH\Asana\Requests\Goals\GetGoalsRequest;
use WMBH\Asana\Requests\Goals\GetSubgoalsRequest;
use WMBH\Asana\Requests\Goals\UpdateGoalMetricRequest;
use WMBH\Asana\Requests\Goals\UpdateGoalRequest;

class GoalResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): GoalData
    {
        $response = $this->connector->send(new GetGoalRequest($gid, $optFields));

        return GoalData::from($response->json('data'));
    }

    public function list(array $params = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetGoalsRequest($params));

        return PaginatedResponse::fromResponse($response->json(), GoalData::class);
    }

    public function create(array $data): GoalData
    {
        $response = $this->connector->send(new CreateGoalRequest($data));

        return GoalData::from($response->json('data'));
    }

    public function update(string $gid, array $data): GoalData
    {
        $response = $this->connector->send(new UpdateGoalRequest($gid, $data));

        return GoalData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $this->connector->send(new DeleteGoalRequest($gid));

        return true;
    }

    public function getSubgoals(string $goalGid): PaginatedResponse
    {
        $response = $this->connector->send(new GetSubgoalsRequest($goalGid));

        return PaginatedResponse::fromResponse($response->json(), GoalData::class);
    }

    public function addSubgoal(string $goalGid, string $subgoalGid): bool
    {
        $this->connector->send(new AddSubgoalRequest($goalGid, $subgoalGid));

        return true;
    }

    public function getRelationships(string $goalGid): PaginatedResponse
    {
        $response = $this->connector->send(new GetGoalRelationshipsRequest($goalGid));

        return PaginatedResponse::fromResponse($response->json(), CompactResource::class);
    }

    public function updateMetric(string $goalGid, array $data): GoalData
    {
        $response = $this->connector->send(new UpdateGoalMetricRequest($goalGid, $data));

        return GoalData::from($response->json('data'));
    }
}
