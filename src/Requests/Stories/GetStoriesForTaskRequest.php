<?php

namespace WMBH\Asana\Requests\Stories;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetStoriesForTaskRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $taskGid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tasks/{$this->taskGid}/stories";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
