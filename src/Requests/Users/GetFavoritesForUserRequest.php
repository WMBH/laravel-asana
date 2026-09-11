<?php

namespace WMBH\Asana\Requests\Users;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetFavoritesForUserRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $userGid,
        protected readonly string $resourceType,
        protected readonly string $workspaceGid,
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/users/{$this->userGid}/favorites";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'resource_type' => $this->resourceType,
            'workspace' => $this->workspaceGid,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
