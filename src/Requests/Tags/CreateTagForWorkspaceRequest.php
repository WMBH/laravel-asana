<?php

namespace WMBH\Asana\Requests\Tags;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateTagForWorkspaceRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $workspaceGid,
        protected readonly array $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/workspaces/{$this->workspaceGid}/tags";
    }

    protected function defaultBody(): array
    {
        return ['data' => $this->data];
    }
}
