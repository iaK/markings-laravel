<?php

namespace Tests\TestClasses\Events;

use Tests\TestClasses\Models\Nested\Nested;
use Tests\TestClasses\Models\User;

class UserCreatedEvent
{
    public function __construct(
        public User $coolUser,
        public ?Nested $megaNested,
        public int $name,
        public $noType,
        public \Closure $callback,
    )
    {
        
    }
}
