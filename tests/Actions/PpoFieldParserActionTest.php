<?php

use Markings\Actions\PpoFieldParserAction;
use Tests\TestClasses\DataTransferObjects\Order;

it('it can parse fields', function () {
    [$fields, $skippedFields] = PpoFieldParserAction::make()->handle(
        new ReflectionClass(Order::class)
    );

    $this->assertEquals(getField('item', $fields), [
        'name' => 'item',
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
    $this->assertEquals(getField('created_at', $fields), [
        'name' => 'created_at',
        'nullable' => false,
        'type' => 'datetime',
    ]);
});

it('skips types id doesnt recognize', function () {
    [$fields, $skippedFields] = PpoFieldParserAction::make()->handle(
        new ReflectionClass(Order::class)
    );

    $this->assertEquals(['Order' => 'user'], $skippedFields);
});
