<?php

use Markings\Actions\PopoFieldParserAction;
use Tests\TestClasses\DataTransferObjects\Order;

it('it can parse fields', function () {
    [$fields, $skippedFields] = PopoFieldParserAction::make()->handle(
        new ReflectionClass(Order::class)
    );

    $this->assertCount(10, $fields);
    $this->assertEquals(getField('items', $fields), [
        'name' => 'items',
        'nullable' => false,
        'type' => 'custom',
    ]);
    $this->assertEquals(getField('ints', $fields), [
        'name' => 'ints',
        'nullable' => false,
        'type' => 'integer',
    ]);
    $this->assertEquals(getField('strings', $fields), [
        'name' => 'strings',
        'nullable' => false,
        'type' => 'string',
    ]);
    $this->assertEquals(getField('comment', $fields), [
        'name' => 'comment',
        'nullable' => true,
        'type' => 'string',
    ]);
    $this->assertEquals(getField('quantity', $fields), [
        'name' => 'quantity',
        'nullable' => false,
        'type' => 'integer',
    ]);
    $this->assertEquals(getField('price', $fields), [
        'name' => 'price',
        'nullable' => false,
        'type' => 'float',
    ]);
    $this->assertEquals(getField('is_paid', $fields), [
        'name' => 'is_paid',
        'nullable' => false,
        'type' => 'boolean',
    ]);
    $this->assertEquals(getField('unknown', $fields), [
        'name' => 'unknown',
        'nullable' => false,
        'type' => 'string',
    ]);
    $this->assertEquals(getField('created_at', $fields), [
        'name' => 'created_at',
        'nullable' => false,
        'type' => 'datetime',
    ]);
    $this->assertEquals(getField('user', $fields), [
        'name' => 'user',
        'nullable' => false,
        'type' => 'custom',
    ]);
});

it('skips types id doesnt recognize', function () {
    [$fields, $skippedFields] = PopoFieldParserAction::make()->handle(
        new ReflectionClass(Order::class)
    );
    $this->assertEquals(['Order' => 'arrays'], $skippedFields);
});
