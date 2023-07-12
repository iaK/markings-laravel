<?php

namespace Markings\Actions;

use Mockery;
use ReflectionClass;
use Mockery\MockInterface;
use Illuminate\Support\Str;
use Mockery\LegacyMockInterface;
use Illuminate\Support\Facades\App;

abstract class Action
{
    public static function fake(): MockInterface | LegacyMockInterface
    {
        return tap(Mockery::mock(static::class), function ($mock) {
            App::instance(
                static::class,
                $mock
            );
        });
    }

    /**
     * @return static
     */
    public static function make()
    {
        return app(static::class);
    }

    /**
     * @debt-checked - CB-1112
     * @deprecated Use Action::make()->handle() instead.
     */
    public static function execute()
    {
        $reflectionClass = new ReflectionClass(static::class);

        if ($reflectionClass->hasMethod('handle')) {
            /* @phpstan-ignore-next-line */
            return static::make()->handle(...func_get_args());
        }

        return resolve(static::class)(...func_get_args());
    }

    public function slug(): string
    {
        return (string)Str::of(static::class)
            ->afterLast('\\')
            ->kebab();
    }
}
