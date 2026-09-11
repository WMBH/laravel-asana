<?php

namespace WMBH\Asana\Requests\Workspaces;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class TypeaheadRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly string $resourceType,
        protected readonly ?string $search = null,
        protected readonly ?int $count = null,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/typeahead";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'resource_type' => $this->resourceType,
            'query' => $this->search,
            'count' => $this->count,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
