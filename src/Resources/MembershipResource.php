<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\MembershipData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\Memberships\CreateMembershipRequest;
use WMBH\Asana\Requests\Memberships\DeleteMembershipRequest;
use WMBH\Asana\Requests\Memberships\GetMembershipRequest;
use WMBH\Asana\Requests\Memberships\GetMembershipsRequest;
use WMBH\Asana\Requests\Memberships\UpdateMembershipRequest;

class MembershipResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function list(?string $parentGid = null, ?string $memberGid = null, ?string $resourceSubtype = null, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetMembershipsRequest($parentGid, $memberGid, $resourceSubtype, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), MembershipData::class);
    }

    public function get(string $gid): MembershipData
    {
        $response = $this->connector->send(new GetMembershipRequest($gid));

        return MembershipData::from($response->json('data'));
    }

    public function create(array $data): MembershipData
    {
        $response = $this->connector->send(new CreateMembershipRequest($data));

        return MembershipData::from($response->json('data'));
    }

    public function update(string $gid, array $data): MembershipData
    {
        $response = $this->connector->send(new UpdateMembershipRequest($gid, $data));

        return MembershipData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $response = $this->connector->send(new DeleteMembershipRequest($gid));

        return $response->status() === 200;
    }
}
