<?php

namespace WMBH\Asana\Requests\Goals;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteGoalRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/goals/{$this->gid}";
    }
}
