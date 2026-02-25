<?php

namespace WMBH\Asana\Requests\CustomFields;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteCustomFieldRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/custom_fields/{$this->gid}";
    }
}
