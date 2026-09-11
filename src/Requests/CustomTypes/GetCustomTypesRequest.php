<?php

namespace WMBH\Asana\Requests\CustomTypes;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetCustomTypesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly ?string $projectGid = null,
        protected readonly ?string $workspaceGid = null,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/custom_types';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'project' => $this->projectGid,
            'workspace' => $this->workspaceGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
