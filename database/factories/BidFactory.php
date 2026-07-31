<?php

namespace Database\Factories;

use App\Enums\BidStatus;
use App\Models\Bid;
use App\Models\Domain;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Bid>
 */
class BidFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'domain_id' => Domain::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'amount' => fake()->numberBetween(100, 50000),
            'status' => BidStatus::Pending,
            'email_verified_at' => null,
            'verification_token' => Str::random(64),
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (): array => [
            'email_verified_at' => now(),
            'verification_token' => null,
        ]);
    }

    public function accepted(): static
    {
        return $this->verified()->state(fn (): array => [
            'status' => BidStatus::Accepted,
        ]);
    }

    public function rejected(): static
    {
        return $this->verified()->state(fn (): array => [
            'status' => BidStatus::Rejected,
        ]);
    }
}
