<?php

namespace WMBH\Asana\Requests\CustomTypes;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetCustomTypeRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/custom_types/{$this->gid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
