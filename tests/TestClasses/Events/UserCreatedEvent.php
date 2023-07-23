<?php

namespace Tests\TestClasses\Events;

use Tests\TestClasses\Models\Nested\Nested;
use Tests\TestClasses\Models\User;

class UserCreatedEvent
{
    /**
     * @var array<User> $users
     */
    public array $users;

    /**   
     * @var array<int> $ints
     */
    public array $ints;
    /**
     * @var array<array<string>> $arrays
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
