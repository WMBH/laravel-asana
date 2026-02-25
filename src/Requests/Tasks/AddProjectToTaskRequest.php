<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class AddProjectToTaskRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $taskGid,
        protected readonly string $projectGid,
        protected readonly ?string $sectionGid = null,
        protected readonly ?string $insertBefore = null,
        protected readonly ?string $insertAfter = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tasks/{$this->taskGid}/addProject";
    }

    protected function defaultBody(): array
    {
        return ['data' => array_filter([
            'project' => $this->projectGid,
            'section' => $this->sectionGid,
            'insert_before' => $this->insertBefore,
            'insert_after' => $this->insertAfter,
        ])];
    }
}
