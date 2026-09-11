<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class JobData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $resource_subtype = null,
        public readonly ?string $status = null,
        public readonly ?CompactResource $new_project = null,
        public readonly ?CompactResource $new_task = null,
        public readonly ?CompactResource $new_portfolio = null,
        public readonly ?CompactResource $new_project_template = null,
        public readonly ?array $new_graph_export = null,
        public readonly ?array $new_resource_export = null,
    ) {}
}
