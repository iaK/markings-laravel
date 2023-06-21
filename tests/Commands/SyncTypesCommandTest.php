<?php

use Schema;
use Tests\TestClasses\Models\User;
use Illuminate\Support\Facades\Config;

it('can do stuff', function () {
    Config::set('template-genius.types_paths', ['tests/TestClasses/Models']);

    $this->artisan('template-genius:sync-types');
});
