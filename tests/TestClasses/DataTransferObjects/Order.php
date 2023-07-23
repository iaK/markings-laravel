<?php

namespace Tests\TestClasses\DataTransferObjects;

use Tests\TestClasses\Models\User;

class Order
{
    /**
     * @var array<OrderRow>
     */
    public array $items;

    /**
     * @var array<int>
     */
    public array $ints;

    public array $strings;

    /**
     * @var array<array<string>>
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
