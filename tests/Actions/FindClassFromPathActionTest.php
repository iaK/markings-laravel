<?php

use Markings\Actions\FindClassFromPathAction;
use Tests\TestClasses\DataTransferObjects\Order;

it('can find a class', function () {
    $class = FindClassFromPathAction::make()->handle(__DIR__.'/../TestClasses/DataTransferObjects/Order.php');

    expect($class->getName())->toBe(Order::class);
});
