<?php

namespace WMBH\Asana\Requests\Goals;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UpdateGoalMetricRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $goalGid,
        protected readonly array $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/goals/{$this->goalGid}/setMetricCurrentValue";
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
