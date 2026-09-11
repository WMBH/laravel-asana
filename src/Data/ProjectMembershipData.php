<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class ProjectMembershipData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $resource_subtype = null,
        public readonly ?CompactResource $parent = null,
        public readonly ?CompactResource $member = null,
        public readonly ?string $access_level = null,
        public readonly ?CompactResource $user = null,
        public readonly ?CompactResource $project = null,
        public readonly ?string $write_access = null,
    ) {}
}
