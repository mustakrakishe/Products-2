<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currencies = Currency::all('id');

        return [
            'title'       => fake()->word(),
            'price'       => fake()->randomFloat(2, 1, 200),
            'currency_id' => $currencies->isNotEmpty() ? $currencies->random() : Currency::factory(),
        ];
    }
}
