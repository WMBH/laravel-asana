<?php

namespace WMBH\Asana\Requests\Users;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetMeRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return '/users/me';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
