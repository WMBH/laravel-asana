<?php

namespace WMBH\Asana;

use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Response;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;
use WMBH\Asana\Exceptions\AsanaException;
use WMBH\Asana\Exceptions\AuthenticationException;
use WMBH\Asana\Exceptions\ForbiddenException;
use WMBH\Asana\Exceptions\NotFoundException;
use WMBH\Asana\Exceptions\RateLimitException;
use WMBH\Asana\Exceptions\ValidationException;

class AsanaConnector extends Connector
{
    use AlwaysThrowOnErrors;

    public function __construct(
        protected readonly string $token,
        protected readonly int $timeout = 30,
        protected readonly int $retryAttempts = 3,
        protected readonly int $retrySleep = 1000,
    ) {}

    public function resolveBaseUrl(): string
    {
        return 'https://app.asana.com/api/1.0';
    }

    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator($this->token);
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout' => $this->timeout,
        ];
    }

    public function getRequestException(Response $response, ?\Throwable $senderException): ?\Throwable
    {
        return match ($response->status()) {
            400 => ValidationException::fromResponse($response),
            401 => AuthenticationException::fromResponse($response),
            403 => ForbiddenException::fromResponse($response),
            404 => NotFoundException::fromResponse($response),
            429 => RateLimitException::fromResponse($response),
            default => AsanaException::fromResponse($response),
        };
    }
}
