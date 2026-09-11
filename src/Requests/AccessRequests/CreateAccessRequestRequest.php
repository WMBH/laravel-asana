<?php

namespace WMBH\Asana\Requests\AccessRequests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateAccessRequestRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $targetGid,
        protected readonly ?string $message = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/access_requests';
    }

    protected function defaultBody(): array
    {
        return ['data' => array_filter([
            'target' => $this->targetGid,
            'message' => $this->message,
        ])];
    }
}
