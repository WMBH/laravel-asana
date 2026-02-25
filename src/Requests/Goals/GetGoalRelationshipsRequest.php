<?php

namespace WMBH\Asana\Requests\Goals;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetGoalRelationshipsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $goalGid,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/goal_relationships';
    }

    protected function defaultQuery(): array
    {
        return [
            'supported_goal' => $this->goalGid,
        ];
    }
}
