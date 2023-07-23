<?php

namespace Tests\TestClasses\Events;

use Tests\TestClasses\Models\Nested\Nested;
use Tests\TestClasses\Models\User;

class UserCreatedEvent
{
    /**
     * @var array<User>
     */
    public array $users;

    /**
     * @var array<int>
     */
    public array $ints;

    /**
     * @var array<array<string>>
     */
    public array $arrays;

    public array $strings;

    public User $coolUser;

    public Nested $nested;

    public ?Nested $nullNested;

    public int $name;

    public $noType;

    public \Closure $callback;
}
