<?php

namespace WMBH\Asana\Requests\AccessRequests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetAccessRequestsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $targetGid,
        protected readonly ?string $userGid = null,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return '/access_requests';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'target' => $this->targetGid,
            'user' => $this->userGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
