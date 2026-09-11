<?php

namespace WMBH\Asana\Requests\Memberships;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetMembershipRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/memberships/{$this->gid}";
    }
}
