<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetProjectsForTeamRequest extends Request
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
        return "/teams/{$this->teamGid}/projects";
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
