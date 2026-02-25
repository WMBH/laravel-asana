<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTasksForSectionRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $sectionGid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/sections/{$this->sectionGid}/tasks";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
