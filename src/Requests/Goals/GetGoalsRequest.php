<?php

namespace WMBH\Asana\Requests\Goals;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetGoalsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly array $params = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return '/goals';
    }

    protected function defaultQuery(): array
    {
        return array_filter($this->params);
    }
}
