<?php

namespace WMBH\Asana\Requests\Tags;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTagsForWorkspaceRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/tags";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
