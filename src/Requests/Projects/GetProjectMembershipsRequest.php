<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetProjectMembershipsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $projectGid,
        protected readonly ?string $userGid = null,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->projectGid}/project_memberships";
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
