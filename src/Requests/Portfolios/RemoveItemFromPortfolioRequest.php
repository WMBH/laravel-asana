<?php

namespace WMBH\Asana\Requests\Portfolios;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class RemoveItemFromPortfolioRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $portfolioGid,
        protected readonly string $itemGid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/portfolios/{$this->portfolioGid}/removeItem";
    }

    protected function defaultBody(): array
    {
        return ['data' => ['item' => $this->itemGid]];
    }
}
