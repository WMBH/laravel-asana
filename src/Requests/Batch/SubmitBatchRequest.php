<?php

namespace WMBH\Asana\Requests\Batch;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class SubmitBatchRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly array $actions,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/batch';
    }

    protected function defaultBody(): array
    {
        return ['data' => ['actions' => $this->actions]];
    }
}
