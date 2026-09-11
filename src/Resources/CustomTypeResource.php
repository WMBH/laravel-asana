<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\CustomTypeData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\CustomTypes\GetCustomTypeRequest;
use WMBH\Asana\Requests\CustomTypes\GetCustomTypesRequest;

class CustomTypeResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function list(?string $projectGid = null, ?string $workspaceGid = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetCustomTypesRequest($projectGid, $workspaceGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), CustomTypeData::class);
    }

    public function get(string $gid, array $optFields = []): CustomTypeData
    {
        $response = $this->connector->send(new GetCustomTypeRequest($gid, $optFields));

        return CustomTypeData::from($response->json('data'));
    }
}
