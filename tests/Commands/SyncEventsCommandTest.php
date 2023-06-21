<?php

use Illuminate\Support\Facades\Config;

it('can do event stuff', function () {
    Config::set('template-genius.events_paths', ['tests/TestClasses/Events']);

    $this->artisan('template-genius:sync-events');
});
