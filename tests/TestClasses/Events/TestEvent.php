<?php

namespace Tests\TestClasses\Events;

use Tests\TestClasses\Models\User;

class TestEvent
{
    public array $items;

    public string $comment;

    /**
     * @var array<User>
     */
    public array $users;

    public User $user;
}
