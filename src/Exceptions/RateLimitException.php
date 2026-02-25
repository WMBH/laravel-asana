<?php

namespace WMBH\Asana\Exceptions;

use Saloon\Http\Response;

class RateLimitException extends AsanaException
{
    protected int $retryAfter = 60;

    public static function fromResponse(Response $response): static
    {
        $exception = parent::fromResponse($response);
        $exception->retryAfter = (int) ($response->header('Retry-After') ?: 60);

        return $exception;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
