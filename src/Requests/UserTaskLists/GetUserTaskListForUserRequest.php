<?php

namespace WMBH\Asana\Requests\UserTaskLists;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetUserTaskListForUserRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $userGid,
        protected readonly string $workspaceGid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/users/{$this->userGid}/user_task_list";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'workspace' => $this->workspaceGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
