<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SearchTasksRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly array $params = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/tasks/search";
    }

    protected function defaultQuery(): array
    {
        return $this->params;
    }
}
