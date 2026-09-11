<?php

namespace WMBH\Asana\Requests\ProjectTemplates;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteProjectTemplateRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/project_templates/{$this->gid}";
    }
}
