<?php

namespace WMBH\Asana\Requests\Attachments;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteAttachmentRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/attachments/{$this->gid}";
    }
}
