<?php

namespace Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory;

    public $appends = ['full_name', 'fuller_name'];

    public function getFullerNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['first_name'].' '.$attributes['last_name'],
        );
    }
}
