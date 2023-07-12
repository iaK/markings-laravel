<?php

namespace Markings;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Markings\Commands\InstallCommand;
use Markings\Commands\SyncAllCommand;
use Markings\Commands\SyncEventsCommand;
use Markings\Commands\SyncTypesCommand;
use Markings\Listeners\SendMailListener;

class MarkingsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('markings-laravel')
            ->hasConfigFile('markings')
            ->hasCommand(InstallCommand::class)
            ->hasCommand(SyncAllCommand::class)
            ->hasCommand(SyncTypesCommand::class)
            ->hasCommand(SyncEventsCommand::class);
    }

    public function packageBooted()
    {
        $this->app['events']->listen(
            '*',
            SendMailListener::class
        );
    }
}
