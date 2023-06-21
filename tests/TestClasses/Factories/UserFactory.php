<?php

namespace Tests\TestClasses\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\TestClasses\Models\User;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'password' => '::secret::',
            'age' => $this->faker->numberBetween(1, 100),
        ];
    }
}
