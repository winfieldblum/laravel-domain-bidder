<?php

namespace Database\Factories;

use App\Models\Domain;
use App\Models\DomainSellingPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DomainSellingPoint>
 */
class DomainSellingPointFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'domain_id' => Domain::factory(),
            'text' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
