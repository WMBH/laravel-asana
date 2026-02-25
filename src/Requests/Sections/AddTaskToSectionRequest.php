<?php

namespace WMBH\Asana\Requests\Sections;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class AddTaskToSectionRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $sectionGid,
        protected readonly string $taskGid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/sections/{$this->sectionGid}/addTask";
    }

    protected function defaultBody(): array
    {
        return ['data' => ['task' => $this->taskGid]];
    }
}
