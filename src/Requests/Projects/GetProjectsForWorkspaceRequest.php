<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetProjectsForWorkspaceRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
        protected readonly ?bool $archived = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/projects";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'archived' => $this->archived,
        ], fn ($value) => $value !== null);
    }
}
