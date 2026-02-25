<?php

namespace WMBH\Asana\Requests\CustomFields;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetCustomFieldRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/custom_fields/{$this->gid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
