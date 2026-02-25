<?php

namespace WMBH\Asana\Requests\Attachments;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateAttachmentRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $parentGid,
        protected readonly array $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/attachments';
    }

    protected function defaultBody(): array
    {
        return ['data' => array_merge(['parent' => $this->parentGid], $this->data)];
    }
}
