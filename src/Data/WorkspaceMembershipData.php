<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class WorkspaceMembershipData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?CompactResource $user = null,
        public readonly ?CompactResource $workspace = null,
        public readonly ?CompactResource $user_task_list = null,
        public readonly ?bool $is_active = null,
        public readonly ?bool $is_admin = null,
        public readonly ?bool $is_guest = null,
        public readonly ?bool $is_view_only = null,
        public readonly ?array $vacation_dates = null,
        public readonly ?string $created_at = null,
    ) {}
}
