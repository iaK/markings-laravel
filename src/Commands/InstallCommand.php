<?php

namespace TemplateGenius\TemplateGenius\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    public $signature = 'template-genius:install';

    public $description = 'Install Template Genius into your codebase';

    public function handle(): int
    {
        $this->line(' _______                        __         __              _______               __              ');
        $this->line('|_     _|.-----.--------.-----.|  |.---.-.|  |_.-----.    |     __|.-----.-----.|__|.--.--.-----.');
        $this->line('  |   |  |  -__|        |  _  ||  ||  _  ||   _|  -__|    |    |  ||  -__|     ||  ||  |  |__ --|');
        $this->line('  |___|  |_____|__|__|__|   __||__||___._||____|_____|    |_______||_____|__|__||__||_____|_____|');
        $this->line('                        |__|                                                                     ');
        $this->newLine();
        $this->call('vendor:publish', [
            '--tag' => 'template-genius-config',
        ]);
        $this->info('Welcome to Template Genius!');
        $this->newLine();
        $this->info('To get started, you\'ll need an access token. Head over to https://template-genius.com/settings to generate one.');
        $this->newLine();
        $token = $this->ask('Paste your access token here:');
        $this->newLine();
        File::append(base_path('.env'), 'TEMPLATE_GENIUS_API_TOKEN='.$token);
        $this->info('Great! We\'ve added the token to your .env file.');
        $this->newLine();
        $this->confirm('Now, make sure the paths in your config file (template-genius.php) are correct. Update if necessary, and then press enter to continue.');
        $this->newLine();
        if ($this->confirm('Awesome! All thats left is to sync your types & events. Should we do that for you?')) {
            $this->call('template-genius:sync');
        } else {
            $this->info('No problem! You can run the sync command using \'php artisan template-genius:sync\' whenever you\'re ready.');
        }
        $this->newLine();
        $this->info('Thats it! You\'re all set up.');
        $this->newLine();
        $this->info('See you later!');

        return self::SUCCESS;
    }
}
