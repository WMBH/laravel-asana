<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class ProjectBriefData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $title = null,
        public readonly ?string $html_text = null,
        public readonly ?string $text = null,
        public readonly ?string $permalink_url = null,
        public readonly ?CompactResource $project = null,
    ) {}
}
