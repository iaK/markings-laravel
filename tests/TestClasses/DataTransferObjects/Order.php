<?php

namespace Tests\TestClasses\DataTransferObjects;

use Tests\TestClasses\Models\User;
use Tests\TestClasses\DataTransferObjects\OrderRow;

class Order
{
    /**
     * @var array<OrderRow> $items
     */
    public array $items;

    /**
     * @var array<int> $ints
     */
    public array $ints;

    public array $strings;

    /**
     * @var array<array<string>> $arrays
     */
    public array $arrays;

    public ?string $comment;

    public int $quantity;

    public float $price;

    public bool $is_paid;

    public $unknown;

    public \DateTime $created_at;

    public User $user;
}
