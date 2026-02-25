<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;

class CustomFieldData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?string $resource_subtype = null,
        public readonly ?string $type = null,
        public readonly ?string $description = null,
        public readonly ?bool $enabled = null,
        public readonly ?array $enum_options = null,
        public readonly ?int $precision = null,
        public readonly ?string $format = null,
        public readonly ?string $currency_code = null,
        public readonly ?string $custom_label = null,
        public readonly ?string $custom_label_position = null,
        public readonly ?bool $is_global_to_workspace = null,
        public readonly ?bool $has_notifications_enabled = null,
    ) {}
}
