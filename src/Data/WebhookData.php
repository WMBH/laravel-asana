<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class WebhookData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?bool $active = null,
        public readonly ?CompactResource $resource = null,
        public readonly ?string $target = null,
        public readonly ?string $created_at = null,
        public readonly ?string $last_failure_at = null,
        public readonly ?string $last_failure_content = null,
        public readonly ?string $last_success_at = null,
        public readonly ?array $filters = null,
    ) {}
}
