<?php

use Illuminate\Support\Facades\Config;

it('can install', function () {
    $this->artisan('markings:install')
        ->expectsOutput('___  ___           _    _                   _       ')
        ->expectsOutput('|  \/  |          | |  (_)                 (_)      ')
        ->expectsOutput('| .  . | __ _ _ __| | ___ _ __   __ _ ___   _  ___  ')
        ->expectsOutput("| |\/| |/ _` | '__| |/ / | '_ \ / _` / __| | |/ _ \ ")
        ->expectsOutput('| |  | | (_| | |  |   <| | | | | (_| \__ \_| | (_) |')
        ->expectsOutput('\_|  |_/\__,_|_|  |_|\_\_|_| |_|\__, |___(_)_|\___/ ')
        ->expectsOutput('                                 __/ |              ')
        ->expectsOutput('                                |___/               ')
        ->expectsOutput('Welcome to Markings!')
        ->expectsOutput('To get started, you\'ll need an access token. Head over to https://markings.io/settings to generate one.')
        ->expectsQuestion('Paste your access token here', '::ACCESS_TOKEN::')
        ->expectsConfirmation('Now, make sure the paths in your config file (markings.php) are correct. Update if necessary, and then press enter to continue.', 'yes')
        ->expectsConfirmation('Awesome! All thats left is to sync your types & events. Should we do that for you?', 'yes')
        ->expectsOutput('Thats it! You\'re all set up.')
        ->expectsOutput('See you later!');

    $this->assertEquals('::ACCESS_TOKEN::', Config::get('markings.api_token'));
    $this->assertEquals([
        'types_paths' => ['app/Models'],
        'events_paths' => ['app/Events'],
        'exclude_files' => [],
        'api_url' => 'https://markings.io/api/v1/',
        'api_token' => env('MARKINGS_API_TOKEN'),
    ], Config::get('markings'));
});
