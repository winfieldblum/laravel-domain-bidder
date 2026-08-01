<?php

namespace App\Services;

use App\Enums\BidStatus;
use App\Models\Bid;
use App\Models\Domain;
use App\Models\DomainImpression;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DomainMetrics
{
    public function __construct(private BidService $bids) {}

    public function highestAcceptedAmount(Domain $domain): int
    {
        return $this->bids->highestAcceptedAmount($domain);
    }

    public function uniqueBidderCount(Domain $domain): int
    {
        return (int) $domain->bids()
            ->whereNotNull('email')
            ->distinct()
            ->count('email');
    }

    public function pendingCount(Domain $domain): int
    {
        return $domain->bids()
            ->where('status', BidStatus::Pending)
            ->count();
    }

    public function acceptedCount(Domain $domain): int
    {
        return $domain->bids()->accepted()->count();
    }

    public function activeDomainCount(): int
    {
        return Domain::query()->active()->count();
    }

    public function impressionsToday(?Domain $domain = null): int
    {
        return $this->impressionsBetween(now()->startOfDay(), now()->endOfDay(), $domain);
    }

    public function impressionsLastDays(int $days, ?Domain $domain = null): int
    {
        return $this->impressionsBetween(
            now()->subDays($days - 1)->startOfDay(),
            now()->endOfDay(),
            $domain,
        );
    }

    /**
     * @return Collection<string, int> keyed by Y-m-d
     */
    public function dailyImpressionTotals(CarbonInterface $from, CarbonInterface $to, ?Domain $domain = null): Collection
    {
        $countColumn = (new DomainImpression)->qualifyColumn('count');

        $query = DomainImpression::query()
            ->selectRaw('date, coalesce(sum('.$countColumn.'), 0) as total')
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->groupBy('date')
            ->orderBy('date');

        if ($domain !== null) {
            $query->whereBelongsTo($domain);
        }

        $totals = $query
            ->get()
            ->mapWithKeys(function (DomainImpression $row): array {
                $date = $row->date instanceof \DateTimeInterface
                    ? $row->date->format('Y-m-d')
                    : (string) $row->date;

                return [$date => (int) $row->total];
            });

        return $this->fillDailySeries($from, $to, $totals);
    }

    /**
     * One daily series per active domain, keyed by hostname.
     *
     * @return Collection<string, Collection<string, int>>
     */
    public function dailyImpressionTotalsByDomain(CarbonInterface $from, CarbonInterface $to): Collection
    {
        $countColumn = (new DomainImpression)->qualifyColumn('count');
        $domainsTable = (new Domain)->getTable();
        $impressionsTable = (new DomainImpression)->getTable();
        $domains = Domain::query()->active()->orderBy('hostname')->get();

        $rows = DomainImpression::query()
            ->join($domainsTable, "{$impressionsTable}.domain_id", '=', "{$domainsTable}.id")
            ->where("{$domainsTable}.is_active", true)
            ->whereDate("{$impressionsTable}.date", '>=', $from->toDateString())
            ->whereDate("{$impressionsTable}.date", '<=', $to->toDateString())
            ->groupBy("{$domainsTable}.hostname", "{$impressionsTable}.date")
            ->orderBy("{$impressionsTable}.date")
            ->selectRaw("{$domainsTable}.hostname as hostname, {$impressionsTable}.date as date, coalesce(sum({$countColumn}), 0) as total")
            ->get()
            ->groupBy('hostname');

        return $domains->mapWithKeys(function (Domain $domain) use ($from, $to, $rows): array {
            $totals = ($rows[$domain->hostname] ?? collect())
                ->mapWithKeys(function (DomainImpression $row): array {
                    $date = $row->date instanceof \DateTimeInterface
                        ? $row->date->format('Y-m-d')
                        : (string) $row->date;

                    return [$date => (int) $row->total];
                });

            return [$domain->hostname => $this->fillDailySeries($from, $to, $totals)];
        });
    }

    /**
     * @return Builder<Domain>
     */
    public function activeDomainsWithPerformance(): Builder
    {
        $domainsTable = (new Domain)->getTable();
        $countColumn = (new DomainImpression)->qualifyColumn('count');
        $today = now()->toDateString();
        $from7 = now()->subDays(6)->toDateString();
        $from30 = now()->subDays(29)->toDateString();

        return Domain::query()
            ->active()
            ->select("{$domainsTable}.*")
            ->addSelect([
                'impressions_today' => DomainImpression::query()
                    ->selectRaw("coalesce(sum({$countColumn}), 0)")
                    ->whereColumn('domain_id', "{$domainsTable}.id")
                    ->whereDate('date', $today),
                'impressions_7d' => DomainImpression::query()
                    ->selectRaw("coalesce(sum({$countColumn}), 0)")
                    ->whereColumn('domain_id', "{$domainsTable}.id")
                    ->whereDate('date', '>=', $from7),
                'impressions_30d' => DomainImpression::query()
                    ->selectRaw("coalesce(sum({$countColumn}), 0)")
                    ->whereColumn('domain_id', "{$domainsTable}.id")
                    ->whereDate('date', '>=', $from30),
                'impressions_all' => DomainImpression::query()
                    ->selectRaw("coalesce(sum({$countColumn}), 0)")
                    ->whereColumn('domain_id', "{$domainsTable}.id"),
                'unique_bidders_count' => Bid::query()
                    ->selectRaw('count(distinct email)')
                    ->whereColumn('domain_id', "{$domainsTable}.id")
                    ->whereNotNull('email'),
                'highest_accepted' => Bid::query()
                    ->selectRaw('coalesce(max(amount), 0)')
                    ->whereColumn('domain_id', "{$domainsTable}.id")
                    ->where('status', BidStatus::Accepted),
                'pending_bids_count' => Bid::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('domain_id', "{$domainsTable}.id")
                    ->where('status', BidStatus::Pending),
                'accepted_bids_count' => Bid::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('domain_id', "{$domainsTable}.id")
                    ->where('status', BidStatus::Accepted),
            ])
            ->orderBy('hostname');
    }

    /**
     * @param  Collection<string, int>  $totals
     * @return Collection<string, int>
     */
    protected function fillDailySeries(CarbonInterface $from, CarbonInterface $to, Collection $totals): Collection
    {
        $series = collect();
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $series[$key] = (int) ($totals[$key] ?? 0);
            $cursor = $cursor->addDay();
        }

        return $series;
    }

    protected function impressionsBetween(CarbonInterface $from, CarbonInterface $to, ?Domain $domain = null): int
    {
        $countColumn = (new DomainImpression)->qualifyColumn('count');

        $query = DomainImpression::query()
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString());

        if ($domain !== null) {
            $query->whereBelongsTo($domain);
        }

        return (int) $query
            ->selectRaw("coalesce(sum({$countColumn}), 0) as aggregate")
            ->value('aggregate');
    }
}
