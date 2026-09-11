<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class CustomFieldSettingData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?CompactResource $project = null,
        public readonly ?CompactResource $parent = null,
        public readonly ?bool $is_important = null,
        public readonly ?array $custom_field = null,
    ) {}
}
