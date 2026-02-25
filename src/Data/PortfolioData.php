<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class PortfolioData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?string $color = null,
        public readonly ?string $created_at = null,
        public readonly ?CompactResource $created_by = null,
        public readonly ?string $due_on = null,
        public readonly ?string $start_on = null,
        public readonly ?CompactResource $owner = null,
        public readonly ?CompactResource $workspace = null,
        public readonly ?string $permalink_url = null,
        public readonly ?bool $public = null,
        public readonly ?array $members = null,
        public readonly ?array $custom_fields = null,
        public readonly ?array $custom_field_settings = null,
    ) {}
}
