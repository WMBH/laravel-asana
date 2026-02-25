<?php

namespace WMBH\Asana\Data\Shared;

use Spatie\LaravelData\Data;

class CompactResource extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
    ) {}
}
