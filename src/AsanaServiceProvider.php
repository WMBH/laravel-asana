<?php

namespace WMBH\Asana;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use WMBH\Asana\Commands\AsanaTestCommand;

class AsanaServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('asana')
            ->hasConfigFile()
            ->hasCommand(AsanaTestCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(AsanaConnector::class, function () {
            return new AsanaConnector(
                token: config('asana.token', ''),
                timeout: config('asana.timeout', 30),
                retryAttempts: config('asana.retry.attempts', 3),
                retrySleep: config('asana.retry.sleep', 1000),
            );
        });

        $this->app->singleton(Asana::class, function () {
            return new Asana(
                app(AsanaConnector::class),
            );
        });
    }
}
