<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class StoryData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $text = null,
        public readonly ?string $html_text = null,
        public readonly ?string $type = null,
        public readonly ?string $resource_subtype = null,
        public readonly ?string $created_at = null,
        public readonly ?CompactResource $created_by = null,
        public readonly ?CompactResource $target = null,
        public readonly ?bool $is_pinned = null,
        public readonly ?bool $is_edited = null,
        public readonly ?string $sticker_name = null,
    ) {}
}
