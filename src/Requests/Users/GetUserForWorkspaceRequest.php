<?php

namespace WMBH\Asana\Requests\Users;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetUserForWorkspaceRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly string $userGid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/users/{$this->userGid}";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
