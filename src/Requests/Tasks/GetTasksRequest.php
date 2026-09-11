<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTasksRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly array $params = [],
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/tasks';
    }

    protected function defaultQuery(): array
    {
        return array_filter(array_merge($this->params, [
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]));
    }
}
