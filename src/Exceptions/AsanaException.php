<?php

namespace WMBH\Asana\Exceptions;

use Exception;
use Saloon\Http\Response;

class AsanaException extends Exception
{
    protected array $responseBody = [];

    public static function fromResponse(Response $response): static
    {
        $body = $response->json();
        $message = $body['errors'][0]['message'] ?? 'Unknown Asana API error';

        $exception = new static($message, $response->status());
        $exception->responseBody = $body;

        return $exception;
    }

    public function getResponseBody(): array
    {
        return $this->responseBody;
    }
}
