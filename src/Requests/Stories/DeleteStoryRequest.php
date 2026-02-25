<?php

namespace WMBH\Asana\Requests\Stories;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteStoryRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/stories/{$this->gid}";
    }
}
