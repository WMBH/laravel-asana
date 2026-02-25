<?php

namespace WMBH\Asana\Requests\Sections;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class InsertSectionRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $projectGid,
        protected readonly array $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->projectGid}/sections/insert";
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
