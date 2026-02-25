<?php

namespace WMBH\Asana\Requests\CustomFields;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateCustomFieldRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly array $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/custom_fields';
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
