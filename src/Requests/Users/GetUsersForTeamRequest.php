<?php

namespace WMBH\Asana\Requests\Users;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetUsersForTeamRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $teamGid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/teams/{$this->teamGid}/users";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
