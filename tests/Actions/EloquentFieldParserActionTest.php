<?php

use Markings\Markings\Actions\EloquentFieldParserAction;
use Tests\TestClasses\Models\User;

it('it_can_parse_fields', function () {
    [$fields, $skippedFields] = EloquentFieldParserAction::make()->handle(
        new ReflectionClass(User::class)
    );

    $this->assertEmpty($skippedFields);
    $this->assertEquals(getField('first_name', $fields), [
        'name' => 'first_name',
        'nullable' => false,
        'type' => 'string',
    ]);
    $this->assertEquals(getField('last_name', $fields), [
        'name' => 'last_name',
        'nullable' => true,
        'type' => 'string',
    ]);
    $this->assertEquals(getField('email', $fields), [
        'name' => 'email',
        'nullable' => false,
        'type' => 'string',
    ]);
    $this->assertEquals(getField('age', $fields), [
        'name' => 'age',
        'nullable' => false,
        'type' => 'integer',
    ]);
    $this->assertEquals(getField('created_at', $fields), [
        'name' => 'created_at',
        'nullable' => true,
        'type' => 'datetime',
    ]);
    $this->assertEquals(getField('updated_at', $fields), [
        'name' => 'updated_at',
        'nullable' => true,
        'type' => 'datetime',
    ]);
});

it('does not include hidden fields', function() {
    [$fields, $skippedFields] = EloquentFieldParserAction::make()->handle(
        new ReflectionClass(User::class)
    );

    $this->assertEmpty($skippedFields);
    $this->assertNull(getField('password', $fields));
});

// it('does include appended fields', function() {
//     [$fields, $skippedFields] = EloquentFieldParserAction::make()->handle(
//         new ReflectionClass(User::class)
//     );

//     $this->assertEmpty($skippedFields);

//     $this->assertEquals(getField('full_name', $fields), [
//         'name' => 'full_name',
//         'nullable' => true,
//         'type' => 'string',
//     ]);
//     $this->assertEquals(getField('fuller_name', $fields), [
//         'name' => 'fuller_name',
//         'nullable' => true,
//         'type' => 'string',
//     ]);
// });
