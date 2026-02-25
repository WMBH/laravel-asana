<?php

namespace WMBH\Asana\Requests\Attachments;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetAttachmentsForTaskRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $taskGid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tasks/{$this->taskGid}/attachments";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
