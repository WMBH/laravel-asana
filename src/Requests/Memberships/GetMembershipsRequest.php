<?php

namespace WMBH\Asana\Requests\Memberships;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetMembershipsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly ?string $parentGid = null,
        protected readonly ?string $memberGid = null,
        protected readonly ?string $resourceSubtype = null,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/memberships';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'parent' => $this->parentGid,
            'member' => $this->memberGid,
            'resource_subtype' => $this->resourceSubtype,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
