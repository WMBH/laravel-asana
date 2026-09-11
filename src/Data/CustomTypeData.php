<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;

class CustomTypeData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?string $asana_created_type_identifier = null,
        public readonly ?array $status_options = null,
    ) {}
}
