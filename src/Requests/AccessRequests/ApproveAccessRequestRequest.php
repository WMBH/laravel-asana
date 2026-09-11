<?php

namespace WMBH\Asana\Requests\AccessRequests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ApproveAccessRequestRequest extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/access_requests/{$this->gid}/approve";
    }
}
