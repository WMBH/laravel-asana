<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateTaskRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly array $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/tasks';
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
