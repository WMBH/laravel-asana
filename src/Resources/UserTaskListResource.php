<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\UserTaskListData;
use WMBH\Asana\Requests\UserTaskLists\GetUserTaskListForUserRequest;
use WMBH\Asana\Requests\UserTaskLists\GetUserTaskListRequest;

class UserTaskListResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): UserTaskListData
    {
        $response = $this->connector->send(new GetUserTaskListRequest($gid, $optFields));

        return UserTaskListData::from($response->json('data'));
    }

    public function getForUser(string $userGid, string $workspaceGid, array $optFields = []): UserTaskListData
    {
        $response = $this->connector->send(new GetUserTaskListForUserRequest($userGid, $workspaceGid, $optFields));

        return UserTaskListData::from($response->json('data'));
    }
}
