<?php

namespace WMBH\Asana\Query;

use Illuminate\Support\Collection;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Data\Shared\PaginatedResponse;
use WMBH\Asana\Data\TaskData;
use WMBH\Asana\Requests\Tasks\SearchTasksRequest;

class TaskQueryBuilder
{
    protected array $params = [];

    protected array $optFields = [];

    public function __construct(
        protected readonly AsanaConnector $connector,
        protected readonly string $workspaceGid,
    ) {}

    public function where(string $field, mixed $value): static
    {
        $paramMap = [
            'assignee' => 'assignee.any',
            'projects' => 'projects.any',
            'project' => 'projects.any',
            'sections' => 'sections.any',
            'section' => 'sections.any',
            'tags' => 'tags.any',
            'tag' => 'tags.any',
            'completed' => 'completed',
            'is_subtask' => 'is_subtask',
            'resource_subtype' => 'resource_subtype',
        ];

        $param = $paramMap[$field] ?? $field;
        $this->params[$param] = $value;

        return $this;
    }

    public function assignee(string $assigneeGid): static
    {
        $this->params['assignee.any'] = $assigneeGid;

        return $this;
    }

    public function project(string $projectGid): static
    {
        $this->params['projects.any'] = $projectGid;

        return $this;
    }

    public function section(string $sectionGid): static
    {
        $this->params['sections.any'] = $sectionGid;

        return $this;
    }

    public function tag(string $tagGid): static
    {
        $this->params['tags.any'] = $tagGid;

        return $this;
    }

    public function completed(bool $completed = true): static
    {
        $this->params['completed'] = $completed;

        return $this;
    }

    public function modifiedSince(string $datetime): static
    {
        $this->params['modified_on.after'] = $datetime;

        return $this;
    }

    public function dueOn(string $date): static
    {
        $this->params['due_on.on'] = $date;

        return $this;
    }

    public function dueBefore(string $date): static
    {
        $this->params['due_on.before'] = $date;

        return $this;
    }

    public function dueAfter(string $date): static
    {
        $this->params['due_on.after'] = $date;

        return $this;
    }

    public function sortBy(string $field, bool $ascending = true): static
    {
        $this->params['sort_by'] = $field;
        $this->params['sort_ascending'] = $ascending;

        return $this;
    }

    public function fields(string ...$fields): static
    {
        $this->optFields = $fields;

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->params['limit'] = $limit;

        return $this;
    }

    public function get(): Collection
    {
        $paginated = $this->paginate();

        return collect($paginated->data);
    }

    public function paginate(): PaginatedResponse
    {
        $params = $this->params;

        if ($this->optFields) {
            $params['opt_fields'] = implode(',', $this->optFields);
        }

        $response = $this->connector->send(new SearchTasksRequest($this->workspaceGid, $params));

        return PaginatedResponse::fromResponse($response->json(), TaskData::class);
    }
}
