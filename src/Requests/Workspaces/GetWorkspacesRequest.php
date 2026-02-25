<?php

namespace WMBH\Asana\Requests\Workspaces;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetWorkspacesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/workspaces';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
