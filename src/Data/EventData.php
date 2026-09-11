<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class EventData extends Data
{
    public function __construct(
        public readonly ?CompactResource $user = null,
        public readonly ?CompactResource $resource = null,
        public readonly ?string $type = null,
        public readonly ?string $action = null,
        public readonly ?CompactResource $parent = null,
        public readonly ?string $created_at = null,
        public readonly ?array $change = null,
    ) {}
}
