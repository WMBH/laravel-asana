<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTaskCountsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->gid}/task_counts";
    }
}
