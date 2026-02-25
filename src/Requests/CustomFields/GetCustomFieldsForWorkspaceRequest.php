<?php

namespace WMBH\Asana\Requests\CustomFields;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetCustomFieldsForWorkspaceRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/custom_fields";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
