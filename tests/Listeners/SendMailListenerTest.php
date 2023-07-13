<?php

use Illuminate\Support\Facades\Storage;
use Markings\Listeners\SendMailListener;
use Tests\TestClasses\Events\UserCreatedEvent;
use Tests\TestClasses\Models\User;

it('can send events', function () {
    Storage::put('markings-event.json', json_encode([UserCreatedEvent::class]));

    (new SendMailListener())->handle(
        UserCreatedEvent::class,
        [new UserCreatedEvent(
            coolUser: User::factory()->make(),
            megaNested: null,
            name: 1,
            noType: 'hey',
            callback: fn () => null
        ),
        ]);
});
