<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class TeamData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $html_description = null,
        public readonly ?CompactResource $organization = null,
        public readonly ?string $permalink_url = null,
    ) {}
}
