<?php

namespace WMBH\Asana\Requests\Portfolios;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeletePortfolioRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/portfolios/{$this->gid}";
    }
}
