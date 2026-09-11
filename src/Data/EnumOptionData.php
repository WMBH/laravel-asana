<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;

class EnumOptionData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?bool $enabled = null,
        public readonly ?string $color = null,
    ) {}
}
