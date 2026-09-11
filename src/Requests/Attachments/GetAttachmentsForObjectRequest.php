<?php

namespace WMBH\Asana\Requests\Attachments;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetAttachmentsForObjectRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $parentGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/attachments';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'parent' => $this->parentGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
