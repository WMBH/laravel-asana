<?php

namespace WMBH\Asana\Requests\StatusUpdates;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetStatusUpdatesForObjectRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $parentGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
        protected readonly ?string $createdSince = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/status_updates';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'parent' => $this->parentGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'created_since' => $this->createdSince,
        ]);
    }
}
