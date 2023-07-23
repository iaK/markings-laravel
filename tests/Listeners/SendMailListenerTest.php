<?php

use Tests\TestClasses\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Markings\Listeners\SendMailListener;
use Tests\TestClasses\Models\Nested\Nested;
use Tests\TestClasses\Events\UserCreatedEvent;

it('can send events', function () {
    Storage::put('markings-event.json', json_encode([UserCreatedEvent::class]));

    Http::fake([
        rtrim(config('markings.api_url'), '/').'/events' => Http::response([
            'message' => 'Sync successful!',
        ], 200),
    ]);
    $nested = new Nested();
    $nested->name = 'Nested';
    $nested->age = 1;
    $nested->strings = ['hey', 'ho'];
    $nested->closure = fn () => null;

    $event = new UserCreatedEvent();
    $event->users = [$userA = User::factory()->create(), $userB = User::factory()->create()];
    $event->ints = [1, 2, 3];
    $event->arrays = [['hey'], ['ho']];
    $event->strings = ['hey', 'ho'];
    $event->coolUser = $userC = User::factory()->create();
    $event->nullNested = null;
    $event->nested = $nested;
    $event->name = 1;
    $event->noType = 'hey';
    $event->callback = fn () => null;
    
    (new SendMailListener())->handle(
        UserCreatedEvent::class,
        [$event]
    );

    Http::assertSent(function (Request $request) use ($userA, $userB, $userC) {
        $types = json_decode($request->body(), true);

        $this->assertEquals('UserCreatedEvent', $types['event']);

        $this->assertEquals([
            'name' => 'User',
            'as' => 'users',
            'types' => [
                [
                    [
                        'name' => 'first_name',
                        'value' => $userA->first_name,
                    ],
                    [
                        'name' => 'last_name',
                        'value' => $userA->last_name,
                    ],
                    [
                        'name' => 'email',
                        'value' => $userA->email,
                    ],
                    [
                        'name' => 'password',
                        'value' => $userA->password,
                    ],
                    [
                        'name' => 'age',
                        'value' => $userA->age,
                    ],
                    [
                        'name' => 'created_at',
                        'value' => $userA->created_at->toDateTimeString(),
                    ],
                    [
                        'name' => 'updated_at',
                        'value' => $userA->updated_at->toDateTimeString(),
                    ],
                ],
                [
                    [
                        'name' => 'first_name',
                        'value' => $userB->first_name,
                    ],
                    [
                        'name' => 'last_name',
                        'value' => $userB->last_name,
                    ],
                    [
                        'name' => 'email',
                        'value' => $userB->email,
                    ],
                    [
                        'name' => 'password',
                        'value' => $userB->password,
                    ],
                    [
                        'name' => 'age',
                        'value' => $userB->age,
                    ],
                    [
                        'name' => 'created_at',
                        'value' => $userB->created_at->toDateTimeString(),
                    ],
                    [
                        'name' => 'updated_at',
                        'value' => $userB->updated_at->toDateTimeString(),
                    ],
                ],
            ]
        ], $types['types'][0]);
        $this->assertEquals([
            'name' => 'int',
            'as' => 'ints',
            'value' => [1, 2, 3],
        ], $types['types'][1]);
        $this->assertEquals([
            'name' => 'string',
            'as' => 'strings',
            'value' => ['hey', 'ho'],
        ], $types['types'][2]);
        $this->assertEquals([
            'name' => 'User',
            'as' => 'coolUser',
            'types' => [
                [
                    'name' => 'first_name',
                    'value' => $userC->first_name,
                ],
                [
                    'name' => 'last_name',
                    'value' => $userC->last_name,
                ],
                [
                    'name' => 'email',
                    'value' => $userC->email,
                ],
                [
                    'name' => 'password',
                    'value' => $userC->password,
                ],
                [
                    'name' => 'age',
                    'value' => $userC->age,
                ],
                [
                    'name' => 'created_at',
                    'value' => $userC->created_at->toDateTimeString(),
                ],
                [
                    'name' => 'updated_at',
                    'value' => $userC->updated_at->toDateTimeString(),
                ],
            ],
        ], $types['types'][3]);
        $this->assertEquals([
            'name' => 'Nested',
            'as' => 'nested',
            'types' => [
                [
                    'name' => 'string',
                    'as' => 'name',
                    'value' => 'Nested',
                ],
                [
                    'name' => 'int',
                    'as' => 'age',
                    'value' => 1,
                ],
                [
                    'name' => 'string',
                    'as' => 'strings',
                    'value' => ['hey', 'ho'],
                ],
            ],
        ], $types['types'][4]);
        $this->assertEquals([
            'name' => 'int',
            'as' => 'name',
            'value' => 1,
        ], $types['types'][5]);
        $this->assertEquals([
            'name' => 'string',
            'as' => 'noType',
            'value' => 'hey',
        ], $types['types'][6]);

        return true;
    });
});
