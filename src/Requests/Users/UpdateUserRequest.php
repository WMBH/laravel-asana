<?php

namespace WMBH\Asana\Requests\Users;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UpdateUserRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $data,
        protected readonly ?string $workspaceGid = null,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/users/{$this->gid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'workspace' => $this->workspaceGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
