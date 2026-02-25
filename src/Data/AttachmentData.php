<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class AttachmentData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?string $resource_subtype = null,
        public readonly ?string $created_at = null,
        public readonly ?string $download_url = null,
        public readonly ?string $host = null,
        public readonly ?CompactResource $parent = null,
        public readonly ?string $permanent_url = null,
        public readonly ?int $size = null,
        public readonly ?string $view_url = null,
    ) {}
}
