<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class SetParentRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $taskGid,
        protected readonly string $parentGid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tasks/{$this->taskGid}/setParent";
    }

    protected function defaultBody(): array
    {
        return ['data' => ['parent' => $this->parentGid]];
    }
}
