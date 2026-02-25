<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $resource_type = null,
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?array $photo = null,
        public readonly ?array $workspaces = null,
    ) {}
}
