<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\CustomFieldData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\CustomFields\CreateCustomFieldRequest;
use WMBH\Asana\Requests\CustomFields\DeleteCustomFieldRequest;
use WMBH\Asana\Requests\CustomFields\GetCustomFieldRequest;
use WMBH\Asana\Requests\CustomFields\GetCustomFieldsForWorkspaceRequest;
use WMBH\Asana\Requests\CustomFields\UpdateCustomFieldRequest;

class CustomFieldResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): CustomFieldData
    {
        $response = $this->connector->send(new GetCustomFieldRequest($gid, $optFields));

        return CustomFieldData::from($response->json('data'));
    }

    public function getForWorkspace(string $workspaceGid, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetCustomFieldsForWorkspaceRequest($workspaceGid, $optFields));

        return PaginatedResponse::fromResponse($response->json(), CustomFieldData::class);
    }

    public function create(array $data): CustomFieldData
    {
        $response = $this->connector->send(new CreateCustomFieldRequest($data));

        return CustomFieldData::from($response->json('data'));
    }

    public function update(string $gid, array $data): CustomFieldData
    {
        $response = $this->connector->send(new UpdateCustomFieldRequest($gid, $data));

        return CustomFieldData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $this->connector->send(new DeleteCustomFieldRequest($gid));

        return true;
    }
}
