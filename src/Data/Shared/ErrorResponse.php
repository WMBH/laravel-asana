<?php

namespace WMBH\Asana\Data\Shared;

use Spatie\LaravelData\Data;

class ErrorResponse extends Data
{
    public function __construct(
        public readonly array $errors = [],
    ) {}
}
