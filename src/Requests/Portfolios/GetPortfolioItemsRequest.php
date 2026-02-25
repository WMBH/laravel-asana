<?php

namespace WMBH\Asana\Requests\Portfolios;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetPortfolioItemsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $portfolioGid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/portfolios/{$this->portfolioGid}/items";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
