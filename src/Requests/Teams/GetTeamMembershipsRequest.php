<?php

namespace WMBH\Asana\Requests\Teams;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTeamMembershipsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly ?string $teamGid = null,
        protected readonly ?string $userGid = null,
        protected readonly ?string $workspaceGid = null,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/team_memberships';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'team' => $this->teamGid,
            'user' => $this->userGid,
            'workspace' => $this->workspaceGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
