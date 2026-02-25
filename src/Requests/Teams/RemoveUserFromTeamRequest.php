<?php

namespace WMBH\Asana\Requests\Teams;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class RemoveUserFromTeamRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $teamGid,
        protected readonly string $userGid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/teams/{$this->teamGid}/removeUser";
    }

    protected function defaultBody(): array
    {
        return ['data' => ['user' => $this->userGid]];
    }
}
