<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTasksForUserTaskListRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $userTaskListGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
        protected readonly ?string $completedSince = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/user_task_lists/{$this->userTaskListGid}/tasks";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'completed_since' => $this->completedSince,
        ]);
    }
}
