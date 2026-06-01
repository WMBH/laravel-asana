<?php

use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use WMBH\Asana\AsanaConnector;
use WMBH\Asana\Exceptions\AuthenticationException;
use WMBH\Asana\Exceptions\ForbiddenException;
use WMBH\Asana\Exceptions\NotFoundException;
use WMBH\Asana\Exceptions\RateLimitException;
use WMBH\Asana\Exceptions\ValidationException;
use WMBH\Asana\Requests\Users\GetMeRequest;

test('connector resolves correct base URL', function () {
    $connector = new AsanaConnector('test-token');

    expect($connector->resolveBaseUrl())->toBe('https://app.asana.com/api/1.0');
});

test('connector uses token authentication', function () {
    $connector = new AsanaConnector('test-token');

    $reflection = new ReflectionMethod($connector, 'defaultAuth');
    $auth = $reflection->invoke($connector);

    expect($auth)->toBeInstanceOf(TokenAuthenticator::class);
});

test('connector sets default headers', function () {
    $connector = new AsanaConnector('test-token');

    $reflection = new ReflectionMethod($connector, 'defaultHeaders');
    $headers = $reflection->invoke($connector);

    expect($headers)->toHaveKey('Accept', 'application/json')
        ->and($headers)->toHaveKey('Content-Type', 'application/json');
});

test('connector sends request to correct endpoint', function () {
    $mockClient = new MockClient([
        MockResponse::make(['data' => ['gid' => '1']], 200),
    ]);

    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    $connector->send(new GetMeRequest);

    $mockClient->assertSent(GetMeRequest::class);
});

test('401 response throws AuthenticationException', function () {
    $mockClient = new MockClient([
        MockResponse::make(['errors' => [['message' => 'Not Authorized']]], 401),
    ]);

    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    $connector->send(new GetMeRequest);
})->throws(AuthenticationException::class, 'Not Authorized');

test('403 response throws ForbiddenException', function () {
    $mockClient = new MockClient([
        MockResponse::make(['errors' => [['message' => 'Forbidden']]], 403),
    ]);

    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    $connector->send(new GetMeRequest);
})->throws(ForbiddenException::class, 'Forbidden');

test('404 response throws NotFoundException', function () {
    $mockClient = new MockClient([
        MockResponse::make(['errors' => [['message' => 'Not Found']]], 404),
    ]);

    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    $connector->send(new GetMeRequest);
})->throws(NotFoundException::class, 'Not Found');

test('429 response throws RateLimitException', function () {
    $mockClient = new MockClient([
        MockResponse::make(['errors' => [['message' => 'Rate Limit Enforced']]], 429, ['Retry-After' => '30']),
    ]);

    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    try {
        $connector->send(new GetMeRequest);
    } catch (RateLimitException $e) {
        expect($e->getMessage())->toBe('Rate Limit Enforced')
            ->and($e->getRetryAfter())->toBe(30);

        return;
    }

    $this->fail('Expected RateLimitException was not thrown');
});

test('400 response throws ValidationException', function () {
    $mockClient = new MockClient([
        MockResponse::make(['errors' => [['message' => 'Invalid request']]], 400),
    ]);

    $connector = new AsanaConnector('test-token');
    $connector->withMockClient($mockClient);

    try {
        $connector->send(new GetMeRequest);
    } catch (ValidationException $e) {
        expect($e->getMessage())->toBe('Invalid request')
            ->and($e->getErrors())->toBe([['message' => 'Invalid request']]);

        return;
    }

    $this->fail('Expected ValidationException was not thrown');
});
