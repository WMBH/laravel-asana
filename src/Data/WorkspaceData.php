<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;

class WorkspaceData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?bool $is_organization = null,
        public readonly ?array $email_domains = null,
    ) {}
}
