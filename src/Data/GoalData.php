<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class GoalData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?CompactResource $owner = null,
        public readonly ?string $due_on = null,
        public readonly ?string $start_on = null,
        public readonly ?string $html_notes = null,
        public readonly ?string $notes = null,
        public readonly ?string $status = null,
        public readonly ?bool $is_workspace_level = null,
        public readonly ?bool $liked = null,
        public readonly ?array $likes = null,
        public readonly ?array $metric = null,
        public readonly ?CompactResource $team = null,
        public readonly ?CompactResource $workspace = null,
        public readonly ?array $followers = null,
        public readonly ?int $num_likes = null,
    ) {}
}
