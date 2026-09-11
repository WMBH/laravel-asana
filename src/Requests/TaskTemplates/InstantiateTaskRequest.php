<?php

namespace WMBH\Asana\Requests\TaskTemplates;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;
use stdClass;

class InstantiateTaskRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $gid,
        protected readonly ?string $name = null,
        protected readonly array $optFields = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/task_templates/{$this->gid}/instantiateTask";
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'opt_fields' => $this->optFields ? implode(',', $this->optFields) : null,
        ]);
    }

    protected function defaultBody(): array
    {
        // Asana requires "data" to be a JSON object; an empty PHP array would encode as [].
        return ['data' => $this->name === null ? new stdClass : ['name' => $this->name]];
    }
}
