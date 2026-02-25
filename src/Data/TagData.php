<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class TagData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?string $color = null,
        public readonly ?string $notes = null,
        public readonly ?string $created_at = null,
        public readonly ?array $followers = null,
        public readonly ?CompactResource $workspace = null,
        public readonly ?string $permalink_url = null,
    ) {}
}
