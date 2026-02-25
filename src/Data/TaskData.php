<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class TaskData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?string $resource_subtype = null,
        public readonly ?CompactResource $assignee = null,
        public readonly ?CompactResource $assignee_section = null,
        public readonly ?bool $completed = null,
        public readonly ?string $completed_at = null,
        public readonly ?string $created_at = null,
        public readonly ?string $due_on = null,
        public readonly ?string $due_at = null,
        public readonly ?string $start_on = null,
        public readonly ?string $start_at = null,
        public readonly ?string $modified_at = null,
        public readonly ?string $notes = null,
        public readonly ?string $html_notes = null,
        public readonly ?int $num_hearts = null,
        public readonly ?int $num_likes = null,
        public readonly ?bool $is_rendered_as_separator = null,
        public readonly ?CompactResource $parent = null,
        public readonly ?CompactResource $workspace = null,
        public readonly ?string $permalink_url = null,
        public readonly ?array $tags = null,
        public readonly ?array $projects = null,
        public readonly ?array $memberships = null,
        public readonly ?array $followers = null,
        public readonly ?array $custom_fields = null,
    ) {}
}
