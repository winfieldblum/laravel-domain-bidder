<?php

namespace Database\Factories;

use App\Models\Bid;
use App\Models\Domain;
use App\Models\RebidToken;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RebidToken>
 */
class RebidTokenFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'domain_id' => Domain::factory(),
            'triggered_by_bid_id' => function (array $attributes): int {
                return Bid::factory()->accepted()->create([
                    'domain_id' => $attributes['domain_id'],
                ])->id;
            },
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'token' => Str::random(64),
            'expires_at' => now()->addHours(24),
            'used_at' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'expires_at' => now()->subHour(),
        ]);
    }

    public function used(): static
    {
        return $this->state(fn (): array => [
            'used_at' => now(),
        ]);
    }
}
