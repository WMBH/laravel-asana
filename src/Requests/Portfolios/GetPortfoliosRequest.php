<?php

namespace WMBH\Asana\Requests\Portfolios;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetPortfoliosRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly string $ownerGid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return '/portfolios';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'workspace' => $this->workspaceGid,
            'owner' => $this->ownerGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
