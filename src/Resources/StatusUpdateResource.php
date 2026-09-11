<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\StatusUpdateData;
use WMBH\Asana\Requests\StatusUpdates\CreateStatusUpdateRequest;
use WMBH\Asana\Requests\StatusUpdates\DeleteStatusUpdateRequest;
use WMBH\Asana\Requests\StatusUpdates\GetStatusUpdateRequest;
use WMBH\Asana\Requests\StatusUpdates\GetStatusUpdatesForObjectRequest;

class StatusUpdateResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): StatusUpdateData
    {
        $response = $this->connector->send(new GetStatusUpdateRequest($gid, $optFields));

        return StatusUpdateData::from($response->json('data'));
    }

    public function getForObject(string $parentGid, array $optFields = [], ?string $offset = null, ?int $limit = null, ?string $createdSince = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetStatusUpdatesForObjectRequest($parentGid, $optFields, $offset, $limit, $createdSince));

        return PaginatedResponse::fromResponse($response->json(), StatusUpdateData::class);
    }

    public function create(string $parentGid, array $data, array $optFields = []): StatusUpdateData
    {
        $response = $this->connector->send(new CreateStatusUpdateRequest($parentGid, $data, $optFields));

        return StatusUpdateData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $response = $this->connector->send(new DeleteStatusUpdateRequest($gid));

        return $response->status() === 200;
    }
}
