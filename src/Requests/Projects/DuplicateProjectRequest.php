<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class DuplicateProjectRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $gid,
        protected readonly array $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->gid}/duplicate";
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
