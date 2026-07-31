<?php

namespace Database\Factories;

use App\Models\Domain;
use App\Models\DomainFeature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DomainFeature>
 */
class DomainFeatureFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'domain_id' => Domain::factory(),
            'icon' => fake()->randomElement(['Globe', 'Shield', 'TrendingUp', 'Zap', 'Users']),
            'title' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'color' => 'text-blue-500',
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
