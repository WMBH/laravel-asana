<?php

namespace WMBH\Asana\Requests\Goals;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetSubgoalsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $goalGid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/goals/{$this->goalGid}/subgoals";
    }
}
