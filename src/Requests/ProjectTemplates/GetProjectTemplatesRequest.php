<?php

namespace WMBH\Asana\Requests\ProjectTemplates;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetProjectTemplatesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly ?string $workspaceGid = null,
        protected readonly ?string $teamGid = null,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/project_templates';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'workspace' => $this->workspaceGid,
            'team' => $this->teamGid,
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
