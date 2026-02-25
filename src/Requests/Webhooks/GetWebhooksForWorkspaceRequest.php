<?php

namespace WMBH\Asana\Requests\Webhooks;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetWebhooksForWorkspaceRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly ?string $resourceGid = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/webhooks';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'workspace' => $this->workspaceGid,
            'resource' => $this->resourceGid,
        ]);
    }
}
