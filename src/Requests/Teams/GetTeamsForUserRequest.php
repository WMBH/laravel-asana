<?php

namespace WMBH\Asana\Requests\Teams;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTeamsForUserRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $userGid,
        protected readonly string $organizationGid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/users/{$this->userGid}/teams";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'organization' => $this->organizationGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
