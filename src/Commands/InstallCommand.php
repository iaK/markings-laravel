<?php

namespace Markings\Markings\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    public $signature = 'markings:install';

    public $description = 'Install Template Genius into your codebase';

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
            '--tag' => 'markings-config',
        ]);
        $this->info('Welcome to Template Genius!');
        $this->newLine();
        $this->info('To get started, you\'ll need an access token. Head over to https://markings.com/settings to generate one.');
        $this->newLine();
        $token = $this->ask('Paste your access token here:');
        $this->newLine();
        File::append(base_path('.env'), 'TEMPLATE_GENIUS_API_TOKEN="'.$token.'"');
        $this->info('Great! We\'ve added the token to your .env file.');
        $this->newLine();
        $this->confirm('Now, make sure the paths in your config file (markings.php) are correct. Update if necessary, and then press enter to continue.', true);
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
