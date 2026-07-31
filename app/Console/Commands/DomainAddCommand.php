<?php

namespace App\Console\Commands;

use App\Models\Domain;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DomainAddCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'domain:add
                            {hostname : The hostname to sell (e.g. agentic.io)}
                            {--tagline= : Short hero tagline}
                            {--description= : Longer hero description}
                            {--from= : From email address}
                            {--from-name= : From display name}
                            {--notify= : Admin notification email}
                            {--inactive : Create the domain as inactive}';

    /**
     * @var string
     */
    protected $description = 'Add a domain for sale on this bidding site';

    public function handle(): int
    {
        $hostname = $this->normalizeHostname((string) $this->argument('hostname'));

        if (Domain::query()->where('hostname', $hostname)->exists()) {
            $this->error("Domain [{$hostname}] already exists.");

            return self::FAILURE;
        }

        $displayName = Str::of($hostname)->beforeLast('.')->headline()->toString();
        $fromName = $this->option('from-name') ?: $displayName;
        $from = $this->option('from') ?: 'noreply@'.$hostname;

        $domain = Domain::query()->create([
            'hostname' => $hostname,
            'display_name' => $displayName,
            'tagline' => $this->option('tagline') ?: "Premium domain for sale: {$hostname}",
            'description' => $this->option('description') ?: "Make an offer for {$hostname}.",
            'is_active' => ! $this->option('inactive'),
            'mail_from_address' => $from,
            'mail_from_name' => $fromName,
            'notification_email' => $this->option('notify'),
        ]);

        $this->info("Created domain [{$domain->hostname}] (id: {$domain->id}).");
        $this->line('Next steps:');
        $this->line("- Point DNS for {$hostname} at this server.");
        $this->line("- For DDEV local testing, add an additional_hostname and restart.");
        $this->line("- Verify {$from} (or its domain) in Resend.");
        $this->line('- Add features and selling points in Filament admin.');

        return self::SUCCESS;
    }

    protected function normalizeHostname(string $hostname): string
    {
        $hostname = strtolower(trim($hostname));
        $hostname = preg_replace('#^https?://#', '', $hostname) ?? $hostname;
        $hostname = rtrim($hostname, '/');

        if (str_starts_with($hostname, 'www.')) {
            $hostname = substr($hostname, 4);
        }

        return $hostname;
    }
}
