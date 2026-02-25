<?php

namespace WMBH\Asana\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;
use WMBH\Asana\AsanaServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelDataServiceProvider::class,
            AsanaServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('asana.token', 'test-token');
        $app['config']->set('asana.timeout', 30);
        $app['config']->set('asana.retry.attempts', 3);
        $app['config']->set('asana.retry.sleep', 1000);
    }
}
