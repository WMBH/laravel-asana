<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class ProjectTemplateData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $html_description = null,
        public readonly ?bool $public = null,
        public readonly ?CompactResource $owner = null,
        public readonly ?CompactResource $team = null,
        public readonly ?array $requested_dates = null,
        public readonly ?array $requested_roles = null,
        public readonly ?string $color = null,
    ) {}
}
