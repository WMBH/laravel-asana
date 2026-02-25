<?php

namespace WMBH\Asana\Requests\Stories;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateStoryRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $taskGid,
        protected readonly array $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tasks/{$this->taskGid}/stories";
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
