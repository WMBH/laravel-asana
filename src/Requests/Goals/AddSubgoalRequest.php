<?php

namespace WMBH\Asana\Requests\Goals;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class AddSubgoalRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $goalGid,
        protected readonly string $subgoalGid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/goals/{$this->goalGid}/addSupportingRelationship";
    }

    protected function defaultBody(): array
    {
        return ['data' => ['supporting_resource' => $this->subgoalGid]];
    }
}
