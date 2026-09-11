<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class TaskTemplateData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?CompactResource $project = null,
        public readonly ?array $template = null,
        public readonly ?CompactResource $created_by = null,
        public readonly ?string $created_at = null,
    ) {}
}
