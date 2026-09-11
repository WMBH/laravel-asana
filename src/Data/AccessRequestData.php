<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class AccessRequestData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $message = null,
        public readonly ?string $approval_status = null,
        public readonly ?CompactResource $requester = null,
        public readonly ?CompactResource $target = null,
    ) {}
}
