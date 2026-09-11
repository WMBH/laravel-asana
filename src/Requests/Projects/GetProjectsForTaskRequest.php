<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetProjectsForTaskRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $taskGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
        protected readonly ?bool $includeInheritedProjects = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tasks/{$this->taskGid}/projects";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'include_inherited_projects' => $this->includeInheritedProjects,
        ], fn ($value) => $value !== null);
    }
}
