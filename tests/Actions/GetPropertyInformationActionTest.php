<?php

use Markings\Actions\GetPropertyInformationAction;
use Tests\TestClasses\Models\User;

it('can get information about a primitive type', function () {
    $class = new ReflectionClass(new class
    {
        public int $number;
    });

    $information = GetPropertyInformationAction::make()->handle($class->getProperty('number'));

    expect($information)->toBe([
        'name' => 'int',
        'as' => 'number',
        'type' => 'integer',
        'nullable' => false,
        'multiple' => false,
    ]);
});

it('defaults to string if a type is missing', function () {
    $class = new ReflectionClass(new class
    {
        public $number;
    });

    $information = GetPropertyInformationAction::make()->handle($class->getProperty('number'));

    expect($information)->toBe([
        'name' => 'string',
        'as' => 'number',
        'type' => 'string',
        'nullable' => false,
        'multiple' => false,
    ]);
});

it('can get information about a nullable primitive type', function () {
    $class = new ReflectionClass(new class
    {
        public ?int $number;
    });

    $information = GetPropertyInformationAction::make()->handle($class->getProperty('number'));

    expect($information)->toBe([
        'name' => 'int',
        'as' => 'number',
        'type' => 'integer',
        'nullable' => true,
        'multiple' => false,
    ]);
});

it('can get information about a multiple primitive type', function () {
    $class = new ReflectionClass(new class
    {
        /**
         * @var array<int>
         */
        public array $number;
    });

    $information = GetPropertyInformationAction::make()->handle($class->getProperty('number'));

    expect($information)->toBe([
        'name' => 'int',
        'as' => 'number',
        'type' => 'integer',
        'nullable' => false,
        'multiple' => true,
    ]);
});

it('defaults to an array of strings if array dockblock is missing', function () {
    $class = new ReflectionClass(new class
    {
        public array $number;
    });

    $information = GetPropertyInformationAction::make()->handle($class->getProperty('number'));

    expect($information)->toBe([
        'name' => 'string',
        'as' => 'number',
        'type' => 'string',
        'nullable' => false,
        'multiple' => true,
    ]);
});

it('can get information about a custom type', function () {
    $class = new ReflectionClass(new class
    {
        public User $user;
    });

    $information = GetPropertyInformationAction::make()->handle($class->getProperty('user'));

    expect($information)->toBe([
        'name' => 'User',
        'as' => 'user',
        'type' => 'custom',
        'nullable' => false,
        'multiple' => false,
    ]);
});

it('can get information about a multiple custom type', function () {
    $class = new ReflectionClass(new class
    {
        /**
         * @var array<User>
         */
        public array $user;
    });

    $information = GetPropertyInformationAction::make()->handle($class->getProperty('user'));

    expect($information)->toBe([
        'name' => 'User',
        'as' => 'user',
        'type' => 'custom',
        'nullable' => false,
        'multiple' => true,
    ]);
});

it('can get information about a nullable custom type', function () {
    $class = new ReflectionClass(new class
    {
        public ?User $user;
    });

    $information = GetPropertyInformationAction::make()->handle($class->getProperty('user'));

    expect($information)->toBe([
        'name' => 'User',
        'as' => 'user',
        'type' => 'custom',
        'nullable' => true,
        'multiple' => false,
    ]);
});

it('can get information about a multiple nullable custom type', function () {
    $class = new ReflectionClass(new class
    {
        /**
         * @var array<User>
         */
        public ?array $user;
    });

    $information = GetPropertyInformationAction::make()->handle($class->getProperty('user'));

    expect($information)->toBe([
        'name' => 'User',
        'as' => 'user',
        'type' => 'custom',
        'nullable' => true,
        'multiple' => true,
    ]);
});
