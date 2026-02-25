<?php

namespace WMBH\Asana\Exceptions;

class ValidationException extends AsanaException
{
    public function getErrors(): array
    {
        return $this->responseBody['errors'] ?? [];
    }
}
