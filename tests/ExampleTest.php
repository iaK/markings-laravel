<?php

use Tests\TestClasses\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Tests\TestClasses\Events\TestEvent;
use Markings\Listeners\SendMailListener;

it('does stuff', function () {
    Config::set('markings.api_token', '5|wPLaYXVg7MT8j95kQL3XJv1ToYBdFJjEmJhFmkNm');
    Config::set('markings.api_url', 'https://jfa2a8xnae.sharedwithexpose.com/api/v1/');
    Config::set('markings.types_paths', ['tests/TestClasses/Models']);
    Config::set('markings.events_paths', ['tests/TestClasses/Events']);

    $event = new TestEvent;
    $event->users = [User::factory()->create(), User::factory()->create()];
    $event->user = User::factory()->create();
    $event->items = ['hey', 'ho'];
    $event->comment = 'hey';

    (new SendMailListener())->handle(
        TestEvent::class,
        [$event]
    );
});
