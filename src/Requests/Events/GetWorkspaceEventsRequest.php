<?php

namespace WMBH\Asana\Requests\Events;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetWorkspaceEventsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly ?string $sync = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/events";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'sync' => $this->sync,
        ]);
    }
}
