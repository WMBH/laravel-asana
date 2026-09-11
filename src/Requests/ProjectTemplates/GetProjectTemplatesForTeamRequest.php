<?php

namespace WMBH\Asana\Requests\ProjectTemplates;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetProjectTemplatesForTeamRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $teamGid,
        protected readonly array $optFields = [],
        protected readonly ?string $offset = null,
        protected readonly ?int $limit = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/teams/{$this->teamGid}/project_templates";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
            'offset' => $this->offset,
            'limit' => $this->limit,
        ]);
    }
}
