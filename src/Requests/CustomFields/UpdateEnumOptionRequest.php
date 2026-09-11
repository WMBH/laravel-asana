<?php

namespace WMBH\Asana\Requests\CustomFields;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UpdateEnumOptionRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        protected readonly string $enumOptionGid,
        protected readonly array $data,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/enum_options/{$this->enumOptionGid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
