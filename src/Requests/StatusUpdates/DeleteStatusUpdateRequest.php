<?php

namespace WMBH\Asana\Requests\StatusUpdates;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteStatusUpdateRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/status_updates/{$this->gid}";
    }
}
