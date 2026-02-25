<?php

namespace WMBH\Asana\Requests\Sections;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetSectionsForProjectRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected readonly string $projectGid,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->projectGid}/sections";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }
}
