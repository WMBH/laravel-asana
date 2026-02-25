<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\SectionData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\Sections\AddTaskToSectionRequest;
use WMBH\Asana\Requests\Sections\CreateSectionRequest;
use WMBH\Asana\Requests\Sections\DeleteSectionRequest;
use WMBH\Asana\Requests\Sections\GetSectionRequest;
use WMBH\Asana\Requests\Sections\GetSectionsForProjectRequest;
use WMBH\Asana\Requests\Sections\InsertSectionRequest;
use WMBH\Asana\Requests\Sections\UpdateSectionRequest;

class SectionResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): SectionData
    {
        $response = $this->connector->send(new GetSectionRequest($gid, $optFields));

        return SectionData::from($response->json('data'));
    }

    public function getForProject(string $projectGid, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetSectionsForProjectRequest($projectGid, $optFields));

        return PaginatedResponse::fromResponse($response->json(), SectionData::class);
    }

    public function create(string $projectGid, array $data): SectionData
    {
        $response = $this->connector->send(new CreateSectionRequest($projectGid, $data));

        return SectionData::from($response->json('data'));
    }

    public function update(string $gid, array $data): SectionData
    {
        $response = $this->connector->send(new UpdateSectionRequest($gid, $data));

        return SectionData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $response = $this->connector->send(new DeleteSectionRequest($gid));

        return $response->status() === 200;
    }

    public function addTask(string $sectionGid, string $taskGid): void
    {
        $this->connector->send(new AddTaskToSectionRequest($sectionGid, $taskGid));
    }

    public function insertSection(string $projectGid, array $data): void
    {
        $this->connector->send(new InsertSectionRequest($projectGid, $data));
    }
}
