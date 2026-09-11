<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\AccessRequestData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\AccessRequests\ApproveAccessRequestRequest;
use WMBH\Asana\Requests\AccessRequests\CreateAccessRequestRequest;
use WMBH\Asana\Requests\AccessRequests\GetAccessRequestsRequest;
use WMBH\Asana\Requests\AccessRequests\RejectAccessRequestRequest;

class AccessRequestResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function list(string $targetGid, ?string $userGid = null, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetAccessRequestsRequest($targetGid, $userGid, $optFields));

        return PaginatedResponse::fromResponse($response->json(), AccessRequestData::class);
    }

    public function create(string $targetGid, ?string $message = null): AccessRequestData
    {
        $response = $this->connector->send(new CreateAccessRequestRequest($targetGid, $message));

        return AccessRequestData::from($response->json('data'));
    }

    public function approve(string $gid): bool
    {
        $response = $this->connector->send(new ApproveAccessRequestRequest($gid));

        return $response->status() === 200;
    }

    public function reject(string $gid): bool
    {
        $response = $this->connector->send(new RejectAccessRequestRequest($gid));

        return $response->status() === 200;
    }
}
