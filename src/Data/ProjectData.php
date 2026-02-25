<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class ProjectData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?bool $archived = null,
        public readonly ?string $color = null,
        public readonly ?string $created_at = null,
        public readonly ?array $current_status = null,
        public readonly ?array $current_status_update = null,
        public readonly ?string $default_view = null,
        public readonly ?string $due_on = null,
        public readonly ?string $due_date = null,
        public readonly ?string $start_on = null,
        public readonly ?string $modified_at = null,
        public readonly ?string $notes = null,
        public readonly ?string $html_notes = null,
        public readonly ?bool $public = null,
        public readonly ?CompactResource $owner = null,
        public readonly ?CompactResource $team = null,
        public readonly ?CompactResource $workspace = null,
        public readonly ?string $permalink_url = null,
        public readonly ?array $custom_fields = null,
        public readonly ?array $members = null,
        public readonly ?array $followers = null,
    ) {}
}
