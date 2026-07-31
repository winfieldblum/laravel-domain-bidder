<?php

namespace Database\Factories;

use App\Models\Domain;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Domain>
 */
class DomainFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hostname = fake()->unique()->domainName();
        $displayName = Str::of($hostname)->beforeLast('.')->headline()->toString();

        return [
            'hostname' => $hostname,
            'display_name' => $displayName,
            'tagline' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'is_active' => true,
            'mail_from_address' => 'noreply@'.$hostname,
            'mail_from_name' => $displayName,
            'notification_email' => fake()->safeEmail(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
