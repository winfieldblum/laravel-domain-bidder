<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\DomainImpression;
use Illuminate\Support\Facades\DB;

class ImpressionService
{
    public function record(Domain $domain, ?string $userAgent = null): void
    {
        if ($this->isBot($userAgent)) {
            return;
        }

        $now = now();
        $countColumn = (new DomainImpression)->qualifyColumn('count');

        DomainImpression::query()->upsert(
            [
                [
                    'domain_id' => $domain->id,
                    'date' => $now->toDateString(),
                    'count' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            uniqueBy: ['domain_id', 'date'],
            update: [
                'count' => DB::raw("{$countColumn} + 1"),
                'updated_at' => $now,
            ],
        );
    }

    protected function isBot(?string $userAgent): bool
    {
        if ($userAgent === null || $userAgent === '') {
            return false;
        }

        return (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview/i', $userAgent);
    }
}
