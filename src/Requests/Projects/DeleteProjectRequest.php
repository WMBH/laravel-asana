<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteProjectRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->gid}";
    }
}
