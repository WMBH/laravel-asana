<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteTaskRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tasks/{$this->gid}";
    }
}
