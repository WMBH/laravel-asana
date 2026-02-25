<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\AttachmentData;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Requests\Attachments\CreateAttachmentRequest;
use WMBH\Asana\Requests\Attachments\DeleteAttachmentRequest;
use WMBH\Asana\Requests\Attachments\GetAttachmentRequest;
use WMBH\Asana\Requests\Attachments\GetAttachmentsForTaskRequest;

class AttachmentResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid, array $optFields = []): AttachmentData
    {
        $response = $this->connector->send(new GetAttachmentRequest($gid, $optFields));

        return AttachmentData::from($response->json('data'));
    }

    public function getForTask(string $taskGid, array $optFields = []): PaginatedResponse
    {
        $response = $this->connector->send(new GetAttachmentsForTaskRequest($taskGid, $optFields));

        return PaginatedResponse::fromResponse($response->json(), AttachmentData::class);
    }

    public function create(string $parentGid, array $data): AttachmentData
    {
        $response = $this->connector->send(new CreateAttachmentRequest($parentGid, $data));

        return AttachmentData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $this->connector->send(new DeleteAttachmentRequest($gid));

        return true;
    }
}
