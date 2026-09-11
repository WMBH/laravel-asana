<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class TeamMembershipData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?CompactResource $user = null,
        public readonly ?CompactResource $team = null,
        public readonly ?bool $is_guest = null,
        public readonly ?bool $is_limited_access = null,
        public readonly ?bool $is_admin = null,
    ) {}
}
