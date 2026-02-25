<?php

namespace WMBH\Asana\Requests\Tags;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteTagRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tags/{$this->gid}";
    }
}
