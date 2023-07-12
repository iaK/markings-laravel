<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Markings\Actions\GetFilesInGlobPatternAction;
use Markings\Exceptions\FilesNotFoundException;

it('can sync events', function () {
    Config::set('markings.events_paths', ['tests/TestClasses/Events']);

    Http::fake();

    $this->artisan('markings:sync-events')
        ->expectsOutput('Event Sync started..')
        ->expectsOutput('Parsing class: Tests\TestClasses\Events\UserCreatedEvent')
        ->expectsOutput('Unknown types found. skipping: UserCreatedEvent: callback')
        ->expectsOutput('Syncing to server..')
        ->expectsOutput('Sync successful!');

    Http::assertSent(function (Request $request) {
        $types = json_decode($request->body(), true);

        $this->assertEquals('UserCreatedEvent', $types['events'][0]['name']);
        $this->assertEquals([
            'name' => 'User',
            'as' => 'coolUser',
            'type' => 'custom',
            'nullable' => false,
        ], $types['events'][0]['types'][0]);
        $this->assertEquals([
            'name' => 'Nested',
            'as' => 'megaNested',
            'type' => 'custom',
            'nullable' => true,
        ], $types['events'][0]['types'][1]);
        $this->assertEquals([
            'name' => 'int',
            'as' => 'name',
            'type' => 'integer',
            'nullable' => false,
        ], $types['events'][0]['types'][2]);
        $this->assertEquals([
            'name' => 'string',
            'as' => 'noType',
            'type' => 'string',
            'nullable' => false,
        ], $types['events'][0]['types'][3]);

        return true;
    });
});

it('fails if the request fails', function () {
    Config::set('markings.events_paths', ['tests/TestClasses/Events']);

    Http::fake([
        '*' => Http::response('error', 500),
    ]);

    $this->artisan('markings:sync-events')
        ->expectsOutput('Event Sync started..')
        ->expectsOutput('Parsing class: Tests\TestClasses\Events\UserCreatedEvent')
        ->expectsOutput('Unknown types found. skipping: UserCreatedEvent: callback')
        ->expectsOutput('Syncing to server..')
        ->expectsOutput('There was an unexpected error when calling the server. Error message:')
        ->expectsOutput('error')
        ->expectsOutput('Sync failed!')
        ->assertExitCode(1);
});

it('gives a helpful message if the glob parsing fails', function () {
    GetFilesInGlobPatternAction::fake()
        ->shouldReceive('handle')
        ->andThrow(new FilesNotFoundException('There was a problem finding your events. Make sure the following glob pattern is correct: tests/TestClasses/Events'));

    $this->artisan('markings:sync-events')
        ->expectsOutput('Event Sync started..')
        ->expectsOutput('There was a problem finding your events. Make sure the following glob pattern is correct: tests/TestClasses/Events')
        ->assertExitCode(1);
});

it('gives a somewhat helpful error message if an unexpected error occurs', function () {
    GetFilesInGlobPatternAction::fake()
        ->shouldReceive('handle')
        ->andThrow(new \Exception('Something went wrong'));

    $this->artisan('markings:sync-events')
        ->expectsOutput('Event Sync started..')
        ->expectsOutput('There was an unexpected error. Error message: Something went wrong')
        ->assertExitCode(1);
});

it('excludes the files in the config', function () {
    Config::set('markings.events_paths', ['tests/TestClasses/Events']);
    Config::set('markings.exclude_files', ['Tests\TestClasses\Events\UserCreatedEvent']);

    Http::fake();

    $this->artisan('markings:sync-events')
        ->expectsOutput('Event Sync started..')
        ->expectsOutput('Skipping class: Tests\TestClasses\Events\UserCreatedEvent')
        ->expectsOutput('Syncing to server..')
        ->expectsOutput('Sync successful!');

    Http::assertSent(function (Request $request) {
        expect($request->body())->toBe('{"events":[]}');

        return true;
    });
});
