<?php

namespace Database\Factories;

use App\Models\Domain;
use App\Models\DomainImpression;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DomainImpression>
 */
class DomainImpressionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'domain_id' => Domain::factory(),
            'date' => now()->toDateString(),
            'count' => fake()->numberBetween(1, 250),
        ];
    }
}
