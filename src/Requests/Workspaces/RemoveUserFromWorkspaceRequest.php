<?php

namespace WMBH\Asana\Requests\Workspaces;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class RemoveUserFromWorkspaceRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly string $userGid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/removeUser";
    }

    protected function defaultBody(): array
    {
        return ['data' => ['user' => $this->userGid]];
    }
}
