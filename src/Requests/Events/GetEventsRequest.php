<?php

namespace WMBH\Asana\Requests\Events;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetEventsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $resourceGid,
        protected readonly ?string $sync = null,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return '/events';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'resource' => $this->resourceGid,
            'sync' => $this->sync,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
