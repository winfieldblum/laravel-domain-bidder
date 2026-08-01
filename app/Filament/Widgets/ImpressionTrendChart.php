<?php

namespace App\Filament\Widgets;

use App\Services\DomainMetrics;
use Filament\Widgets\ChartWidget;

class ImpressionTrendChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Impressions';

    protected ?string $description = 'Daily landing-page views by domain';

    public ?string $filter = '7';

    protected int|string|array $columnSpan = 'full';

    /**
     * @var list<string>
     */
    protected array $seriesColors = [
        '#d97706',
        '#0f766e',
        '#b45309',
        '#1d4ed8',
        '#be123c',
        '#7c3aed',
        '#047857',
        '#c2410c',
    ];

    /**
     * @return array<string, string>
     */
    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
        ];
    }

    protected function getData(): array
    {
        $days = (int) ($this->filter ?: '7');
        $to = now()->endOfDay();
        $from = now()->subDays($days - 1)->startOfDay();

        $byDomain = app(DomainMetrics::class)->dailyImpressionTotalsByDomain($from, $to);

        $labels = [];
        $datasets = [];
        $colorIndex = 0;

        foreach ($byDomain as $hostname => $series) {
            $color = $this->seriesColors[$colorIndex % count($this->seriesColors)];
            $colorIndex++;

            if ($labels === []) {
                $labels = $series->keys()
                    ->map(fn (string $date): string => now()->parse($date)->format('M j'))
                    ->all();
            }

            $datasets[] = [
                'label' => $hostname,
                'data' => $series->values()->all(),
                'borderColor' => $color,
                'backgroundColor' => $color,
                'fill' => false,
                'tension' => 0.3,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
