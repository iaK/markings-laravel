<?php

use Markings\Actions\PopoFieldFinderAction;
use Tests\TestClasses\DataTransferObjects\Order;

it('can find fields', function () {
    [$fields, $skippedFields] = PopoFieldFinderAction::make()->handle(
        new ReflectionClass(Order::class)
    );

    expect(getFieldByAlias('item', $fields))->toBe([
        'name' => 'string',
        'as' => 'item',
        'type' => 'string',
        'nullable' => true,
    ]);
    expect(getFieldByAlias('quantity', $fields))->toBe([
        'name' => 'int',
        'as' => 'quantity',
        'type' => 'integer',
        'nullable' => false,
    ]);
    expect(getFieldByAlias('price', $fields))->toBe([
        'name' => 'float',
        'as' => 'price',
        'type' => 'float',
        'nullable' => false,
    ]);
    expect(getFieldByAlias('is_paid', $fields))->toBe([
        'name' => 'bool',
        'as' => 'is_paid',
        'type' => 'boolean',
        'nullable' => false,
    ]);
    expect(getFieldByAlias('created_at', $fields))->toBe([
        'name' => 'DateTime',
        'as' => 'created_at',
        'type' => 'datetime',
        'nullable' => false,
    ]);
    expect(getFieldByAlias('user', $fields))->toBe([
        'name' => 'User',
        'as' => 'user',
        'type' => 'custom',
        'nullable' => false,
    ]);
});

it('defaults to string', function () {
    [$fields, $skippedFields] = PopoFieldFinderAction::make()->handle(
        new ReflectionClass(Order::class)
    );

    expect(getFieldByAlias('unknown', $fields))->toBe([
        'name' => 'string',
        'as' => 'unknown',
        'type' => 'string',
        'nullable' => false,
    ]);
});

it('skips not allowed types', function () {
    [$fields, $skippedFields] = PopoFieldFinderAction::make()->handle(
        new ReflectionClass(Order::class)
    );

    expect($skippedFields)->toBe([
        'Order' => 'orderRows',
    ]);
});

function getFieldByAlias($name, $fields)
{
    return collect($fields)->first(function ($field) use ($name) {
        return $field['as'] === $name;
    });
}
