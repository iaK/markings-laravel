<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Markings\Markings\Exceptions\FilesNotFoundException;
use Markings\Markings\Actions\GetFilesInGlobPatternAction;

it('can sync events', function () {
    Config::set('markings.events_paths', ['tests/TestClasses/Events']);

    Http::fake();
    
    $this->artisan('markings:sync-events')
        ->expectsOutput('Event Sync started..')
        ->expectsOutput('Parsing file: tests/TestClasses/Events/UserCreatedEvent.php')
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
        ->expectsOutput('Parsing file: tests/TestClasses/Events/UserCreatedEvent.php')
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
