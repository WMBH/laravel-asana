<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class StatusUpdateData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $resource_subtype = null,
        public readonly ?string $title = null,
        public readonly ?string $text = null,
        public readonly ?string $html_text = null,
        public readonly ?string $status_type = null,
        public readonly ?CompactResource $author = null,
        public readonly ?string $created_at = null,
        public readonly ?CompactResource $created_by = null,
        public readonly ?string $modified_at = null,
        public readonly ?bool $hearted = null,
        public readonly ?array $hearts = null,
        public readonly ?bool $liked = null,
        public readonly ?array $likes = null,
        public readonly ?array $reaction_summary = null,
        public readonly ?int $num_hearts = null,
        public readonly ?int $num_likes = null,
        public readonly ?CompactResource $parent = null,
    ) {}
}
