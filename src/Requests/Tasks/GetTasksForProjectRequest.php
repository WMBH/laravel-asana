<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTasksForProjectRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $projectGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->projectGid}/tasks";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
