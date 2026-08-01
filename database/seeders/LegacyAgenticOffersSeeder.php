<?php

namespace Database\Seeders;

use App\Enums\BidStatus;
use App\Models\Bid;
use App\Models\Domain;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class LegacyAgenticOffersSeeder extends Seeder
{
    /**
     * Import historical offers from the legacy agentic.io bidding app.
     */
    public function run(): void
    {
        $domain = Domain::query()->where('hostname', 'agentic.io')->first();

        if ($domain === null) {
            throw new RuntimeException('Domain agentic.io must exist before seeding legacy offers. Run DomainSeeder first.');
        }

        $path = database_path('data/legacy-agentic-offers.json');

        if (! File::exists($path)) {
            throw new RuntimeException("Legacy offers file not found: {$path}");
        }

        /** @var list<array{id: int, name: string, email: string, amount: int, status: string, created_at: string, email_verified: bool, verification_token: ?string}> $offers */
        $offers = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);

        foreach ($offers as $offer) {
            $createdAt = Carbon::parse($offer['created_at']);

            $bid = Bid::query()->firstOrNew(['id' => $offer['id']]);
            $bid->forceFill([
                'domain_id' => $domain->id,
                'name' => trim($offer['name']),
                'email' => strtolower(trim($offer['email'])),
                'amount' => (int) $offer['amount'],
                'status' => BidStatus::from($offer['status']),
                'email_verified_at' => $offer['email_verified'] ? $createdAt : null,
                'verification_token' => $offer['verification_token'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ])->save();
        }

        $this->syncBidIdSequence();
    }

    /**
     * Keep auto-increment / serial above imported legacy IDs.
     */
    protected function syncBidIdSequence(): void
    {
        $maxId = (int) Bid::query()->max('id');

        if ($maxId < 1) {
            return;
        }

        match (DB::getDriverName()) {
            'pgsql' => DB::statement(
                "SELECT setval(pg_get_serial_sequence('bids', 'id'), ?, true)",
                [$maxId],
            ),
            'mysql', 'mariadb' => DB::statement('ALTER TABLE bids AUTO_INCREMENT = '.($maxId + 1)),
            default => null,
        };
    }
}
