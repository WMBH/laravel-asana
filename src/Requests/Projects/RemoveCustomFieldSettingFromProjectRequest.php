<?php

namespace WMBH\Asana\Requests\Projects;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class RemoveCustomFieldSettingFromProjectRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $gid,
        protected readonly string $customFieldGid,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/projects/{$this->gid}/removeCustomFieldSetting";
    }

    protected function defaultBody(): array
    {
        return ['data' => ['custom_field' => $this->customFieldGid]];
    }
}
