<?php

namespace WMBH\Asana\Requests\Webhooks;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteWebhookRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        protected readonly string $gid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/webhooks/{$this->gid}";
    }
}
