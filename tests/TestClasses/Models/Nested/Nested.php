<?php

namespace Tests\TestClasses\Models\Nested;

use Closure;

class Nested
{
    public string $name;

    public ?int $age;

    public Closure $closure;
}
