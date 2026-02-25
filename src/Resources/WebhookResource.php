<?php

namespace WMBH\Asana\Resources;

use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\WebhookData;
use WMBH\Asana\Requests\Webhooks\CreateWebhookRequest;
use WMBH\Asana\Requests\Webhooks\DeleteWebhookRequest;
use WMBH\Asana\Requests\Webhooks\GetWebhookRequest;
use WMBH\Asana\Requests\Webhooks\GetWebhooksForWorkspaceRequest;
use WMBH\Asana\Requests\Webhooks\UpdateWebhookRequest;

class WebhookResource
{
    public function __construct(
        protected readonly AsanaConnector $connector,
    ) {}

    public function get(string $gid): WebhookData
    {
        $response = $this->connector->send(new GetWebhookRequest($gid));

        return WebhookData::from($response->json('data'));
    }

    public function getForWorkspace(string $workspaceGid, ?string $resourceGid = null): PaginatedResponse
    {
        $response = $this->connector->send(new GetWebhooksForWorkspaceRequest($workspaceGid, $resourceGid));

        return PaginatedResponse::fromResponse($response->json(), WebhookData::class);
    }

    public function create(array $data): WebhookData
    {
        $response = $this->connector->send(new CreateWebhookRequest($data));

        return WebhookData::from($response->json('data'));
    }

    public function update(string $gid, array $data): WebhookData
    {
        $response = $this->connector->send(new UpdateWebhookRequest($gid, $data));

        return WebhookData::from($response->json('data'));
    }

    public function delete(string $gid): bool
    {
        $this->connector->send(new DeleteWebhookRequest($gid));

        return true;
    }
}
