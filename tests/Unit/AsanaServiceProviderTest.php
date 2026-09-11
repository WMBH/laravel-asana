<?php

use WMBH\Asana\Asana;
use WMBH\Asana\AsanaConnector;

test('container resolves AsanaConnector as a singleton configured from asana.timeout', function () {
    config()->set('asana.timeout', 45);

    $connector = app(AsanaConnector::class);

    $reflection = new ReflectionMethod($connector, 'defaultConfig');
    $defaultConfig = $reflection->invoke($connector);

    expect($connector)->toBeInstanceOf(AsanaConnector::class)
        ->and($defaultConfig['timeout'])->toBe(45)
        ->and(app(AsanaConnector::class))->toBe($connector);
});

test('container resolves Asana as a singleton wrapping the bound connector', function () {
    $asana = app(Asana::class);

    expect($asana)->toBeInstanceOf(Asana::class)
        ->and($asana->getConnector())->toBe(app(AsanaConnector::class))
        ->and(app(Asana::class))->toBe($asana);
});
