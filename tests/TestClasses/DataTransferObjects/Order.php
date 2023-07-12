<?php

namespace Tests\TestClasses\DataTransferObjects;

use Tests\TestClasses\Models\User;

class Order
{
    public ?string $item;
    public int $quantity;
    public float $price;
    public bool $is_paid;
    public array $orderRows;
    public $unknown;
    public \DateTime $created_at;
    public User $user;
}
