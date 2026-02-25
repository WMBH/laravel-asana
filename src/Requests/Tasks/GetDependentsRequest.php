<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetDependentsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $taskGid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tasks/{$this->taskGid}/dependents";
    }
}
