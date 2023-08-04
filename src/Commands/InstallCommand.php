<?php

namespace Markings\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Markings\Actions\Api;

class InstallCommand extends Command
{
    public $signature = 'markings:install';

    public $description = 'Install Markings into your codebase';

    public function handle(): int
    {
        $this->line('___  ___           _    _                   _       ');
        $this->line('|  \/  |          | |  (_)                 (_)      ');
        $this->line('| .  . | __ _ _ __| | ___ _ __   __ _ ___   _  ___  ');
        $this->line("| |\/| |/ _` | '__| |/ / | '_ \ / _` / __| | |/ _ \ ");
        $this->line('| |  | | (_| | |  |   <| | | | | (_| \__ \_| | (_) |');
        $this->line('\_|  |_/\__,_|_|  |_|\_\_|_| |_|\__, |___(_)_|\___/ ');
        $this->line('                                 __/ |              ');
        $this->line('                                |___/               ');
        $this->newLine();
        $this->call('vendor:publish', [
            '--tag' => 'markings-laravel-config',
        ]);
        $this->info('Welcome to Markings!');
        $this->newLine();
        $this->info('To get started, you\'ll need an access token. Head over to https://markings.io/settings to generate one.');
        $this->newLine();
        $token = $this->ask('Paste your access token here');
        $this->newLine();
        File::append(base_path('.env'), PHP_EOL.'MARKINGS_API_TOKEN="'.$token.'"'.PHP_EOL);
        $this->info('Great! We\'ve added the token to your .env file.');

        $config = require base_path('config/markings.php');

        Config::set('markings', $config);
        Config::set('markings.api_token', $token);

        $environments = resolve(Api::class)->getEnvironments();

        if (count($environments->json()['environments']) > 1) {
            $this->newLine();
            $chosenEnvironment = $this->choice('Which environment would you like to use?', collect($environments->json()['environments'])->map(fn ($e) => $e['name'].($e['main'] ? ' (main)' : ''))->toArray(), 0);
        } else {
            $chosenEnvironment = $environments->json()['environments'][0]['name'];
        }

        $chosenEnvironment = str($chosenEnvironment)->replace(' (main)', '')->trim()->__toString();

        File::put(config_path('markings.php'), str_replace(
            "'initial-environment'",
            "'$chosenEnvironment'",
            File::get(config_path('markings.php'))
        ));

        $this->newLine();
        $this->confirm('Now, make sure the paths in your config file (markings.php) are correct. Update if necessary, and then press enter to continue.', true);

        $config = require base_path('config/markings.php');

        Config::set('markings', $config);
        Config::set('markings.api_token', $token);

        $this->newLine();
        if ($this->confirm('Awesome! All thats left is to sync your types & events. Should we do that for you?', true)) {
            $this->call('markings:sync');
        } else {
            $this->info('No problem! You can run the sync command using \'php artisan markings:sync\' whenever you\'re ready.');
        }
        $this->newLine();
        $this->info('Thats it! You\'re all set up.');
        $this->newLine();
        $this->info('See you later!');

        return self::SUCCESS;
    }
}
