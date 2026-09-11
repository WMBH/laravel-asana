<?php

namespace WMBH\Asana\Requests\TaskTemplates;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteTaskTemplateRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/task_templates/{$this->gid}";
    }
}
