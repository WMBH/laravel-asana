<?php

namespace WMBH\Asana\Requests\Tasks;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class RemoveDependenciesRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $taskGid,
        protected readonly array $dependencyGids,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/tasks/{$this->taskGid}/removeDependencies";
    }

    protected function defaultBody(): array
    {
        return ['data' => ['dependencies' => $this->dependencyGids]];
    }
}
