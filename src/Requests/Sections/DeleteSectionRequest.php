<?php

namespace WMBH\Asana\Requests\Sections;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteSectionRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/sections/{$this->gid}";
    }
}
