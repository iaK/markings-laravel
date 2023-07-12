<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Markings\Exceptions\FilesNotFoundException;
use Markings\Actions\GetFilesInGlobPatternAction;

it('can do sync types', function () {
    Config::set('markings.types_paths', ['tests/TestClasses/Models']);

    Http::fake();
    
    $this->artisan('markings:sync-types')
        ->expectsOutput('Type sync started..')
        ->expectsOutput('Parsing file: tests/TestClasses/Models/User.php')
        ->expectsOutput('Parsing file: tests/TestClasses/Models/Nested/Nested.php')
        ->expectsOutput('Unknown types found. skipping: Nested: closure')
        ->expectsOutput('Syncing to server..')
        ->expectsOutput('Sync successful!');
    

    Http::assertSent(function (Request $request) {
        $types = json_decode($request->body(), true);

        expect($types['types'])->toHaveCount(2);
        expect($types['types'][0]['name'])->toBe('User');
        expect($types['types'][0]['fields'])->toHaveCount(6);
        expect($types['types'][0]['fields'][0]['name'])->toBe('first_name');
        expect($types['types'][0]['fields'][0]['nullable'])->toBe(false);
        expect($types['types'][0]['fields'][0]['type'])->toBe('string');
        expect($types['types'][0]['fields'][1]['name'])->toBe('last_name');
        expect($types['types'][0]['fields'][1]['nullable'])->toBe(true);
        expect($types['types'][0]['fields'][1]['type'])->toBe('string');
        expect($types['types'][0]['fields'][2]['name'])->toBe('email');
        expect($types['types'][0]['fields'][2]['nullable'])->toBe(false);
        expect($types['types'][0]['fields'][2]['type'])->toBe('string');
        expect($types['types'][0]['fields'][3]['name'])->toBe('age');
        expect($types['types'][0]['fields'][3]['nullable'])->toBe(false);
        expect($types['types'][0]['fields'][3]['type'])->toBe('integer');
        expect($types['types'][0]['fields'][4]['name'])->toBe('created_at');
        expect($types['types'][0]['fields'][4]['nullable'])->toBe(true);
        expect($types['types'][0]['fields'][4]['type'])->toBe('datetime');
        expect($types['types'][0]['fields'][5]['name'])->toBe('updated_at');
        expect($types['types'][0]['fields'][5]['nullable'])->toBe(true);
        expect($types['types'][0]['fields'][5]['type'])->toBe('datetime');
        expect($types['types'][1]['name'])->toBe('Nested');
        expect($types['types'][1]['fields'])->toHaveCount(2);
        expect($types['types'][1]['fields'][0]['name'])->toBe('name');
        expect($types['types'][1]['fields'][0]['nullable'])->toBe(false);
        expect($types['types'][1]['fields'][0]['type'])->toBe('string');
        expect($types['types'][1]['fields'][1]['name'])->toBe('age');
        expect($types['types'][1]['fields'][1]['nullable'])->toBe(true);
        expect($types['types'][1]['fields'][1]['type'])->toBe('integer');

        return true;
    });
});


it('fails if the request fails', function () {
    Config::set('markings.types_paths', ['tests/TestClasses/Models']);

    Http::fake([
        '*' => Http::response('error', 500),
    ]);
    
    $this->artisan('markings:sync-types')
        ->expectsOutput('Type sync started..')
        ->expectsOutput('Parsing file: tests/TestClasses/Models/User.php')
        ->expectsOutput('Parsing file: tests/TestClasses/Models/Nested/Nested.php')
        ->expectsOutput('Unknown types found. skipping: Nested: closure')
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
