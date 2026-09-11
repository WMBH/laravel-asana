<?php

namespace WMBH\Asana\Data;

use Spatie\LaravelData\Data;
use WMBH\Asana\Data\Shared\CompactResource;

class ReactionData extends Data
{
    public function __construct(
        public readonly string $gid,
        public readonly ?string $emoji = null,
        public readonly ?CompactResource $user = null,
    ) {}
}
