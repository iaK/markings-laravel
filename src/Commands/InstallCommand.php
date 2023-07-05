<?php

namespace TemplateGenius\TemplateGenius\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    public $signature = 'template-genius:install';

    public $description = 'Install Template Genius into your codebase';

    public function handle(): int
    {
        $this->info('Welcome to Template Genius!');
        $this->info('To get started, you\'ll need an access token. Head over to https://template-genius.com/settings to generate one.');
        $token = $this->ask('Paste your access token here:');
        $this->info('Great! Now add this to your .env file:');
        $this->info('TEMPLATE_GENIUS_API_TOKEN=' . $token);
        $this->info('That\'s pretty much it! Check the config file to see that your Events and Models paths are correct, and then run:');
        $this->info('php artisan template-genius:sync');
        $this->info('See you later!');
    }
}
