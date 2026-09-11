<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchProjectsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly array $params = [],
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/projects/search";
    }

    protected function defaultQuery(): array
    {
        return array_filter(array_merge($this->params, [
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]), fn ($value) => $value !== null);
    }
}
