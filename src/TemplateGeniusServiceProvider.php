<?php

namespace TemplateGenius\TemplateGenius;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use TemplateGenius\TemplateGenius\Commands\InstallCommand;
use TemplateGenius\TemplateGenius\Commands\SyncAllCommand;
use TemplateGenius\TemplateGenius\Commands\SyncTypesCommand;
use TemplateGenius\TemplateGenius\Commands\SyncEventsCommand;

class TemplateGeniusServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('template-genius')
            ->hasConfigFile()
            ->hasCommand(InstallCommand::class)
            ->hasCommand(SyncAllCommand::class)
            ->hasCommand(SyncTypesCommand::class)
            ->hasCommand(SyncEventsCommand::class);
    }
}
