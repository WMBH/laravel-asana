<?php

namespace WMBH\Asana\Requests\Workspaces;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetWorkspaceMembershipsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly ?string $userGid = null,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/workspace_memberships";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'user' => $this->userGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
