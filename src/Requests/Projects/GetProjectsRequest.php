<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetProjectsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/projects';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'workspace' => $this->workspaceGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
