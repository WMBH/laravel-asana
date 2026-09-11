<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\CustomFieldData;
use WMBH\Asana\Data\CustomFieldSettingData;
use WMBH\Asana\Data\EnumOptionData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\CustomFields\CreateCustomFieldRequest;
use WMBH\Asana\Requests\CustomFields\CreateEnumOptionRequest;
use WMBH\Asana\Requests\CustomFields\DeleteCustomFieldRequest;
use WMBH\Asana\Requests\CustomFields\GetCustomFieldRequest;
use WMBH\Asana\Requests\CustomFields\GetCustomFieldSettingsForProjectRequest;
use WMBH\Asana\Requests\CustomFields\GetCustomFieldSettingsForTeamRequest;
use WMBH\Asana\Requests\CustomFields\GetCustomFieldsForWorkspaceRequest;
use WMBH\Asana\Requests\CustomFields\InsertEnumOptionRequest;
use WMBH\Asana\Requests\CustomFields\UpdateCustomFieldRequest;
use WMBH\Asana\Requests\CustomFields\UpdateEnumOptionRequest;

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

    public function getSettingsForProject(string $projectGid, array $optFields = [], ?string $offset = null, ?int $limit = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetCustomFieldSettingsForProjectRequest($projectGid, $optFields, $offset, $limit));

        return PaginatedResponse::fromResponse($response->json(), CustomFieldSettingData::class);
    }

    public function getSettingsForTeam(string $teamGid, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetCustomFieldSettingsForTeamRequest($teamGid, $optFields));

        return PaginatedResponse::fromResponse($response->json(), CustomFieldSettingData::class);
    }

    public function createEnumOption(string $customFieldGid, array $data, array $optFields = []): EnumOptionData
    {
        $response = $this->connector->send(new CreateEnumOptionRequest($customFieldGid, $data, $optFields));

        return EnumOptionData::from($response->json('data'));
    }

    public function insertEnumOption(string $customFieldGid, array $data, array $optFields = []): EnumOptionData
    {
        $response = $this->connector->send(new InsertEnumOptionRequest($customFieldGid, $data, $optFields));

        return EnumOptionData::from($response->json('data'));
    }

    public function updateEnumOption(string $enumOptionGid, array $data, array $optFields = []): EnumOptionData
    {
        $response = $this->connector->send(new UpdateEnumOptionRequest($enumOptionGid, $data, $optFields));

        return EnumOptionData::from($response->json('data'));
    }
}
