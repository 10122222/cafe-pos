<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Customer;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Illuminate\Support\Number;

readonly class DateRange
{
    public function __construct(
        public Carbon $start,
        public Carbon $end,
    ) {}

    public function previous(): self
    {
        $diff = $this->start->diffInDays($this->end);

        return new self(
            $this->start->copy()->subDays($diff)->startOfDay(),
            $this->end->copy()->subDays($diff)->endOfDay()
        );
    }
}

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $dateRange = new DateRange(
            $this->getStartDate()->startOfDay(),
            $this->getEndDate()->endOfDay()
        );

        return [
            $this->createStats(
                __('widgets/stats-overview.stats.revenue.title'),
                'orders',
                'total_price',
                $dateRange,
                fn ($value) => 'Rp ' . $this->formatNumber($value)
            ),
            $this->createStats(
                __('widgets/stats-overview.stats.new_customers.title'),
                'customers',
                null,
                $dateRange
            ),
            $this->createStats(
                __('widgets/stats-overview.stats.new_orders.title'),
                'orders',
                null,
                $dateRange
            ),
        ];
    }

    private function createStats(string $title, string $table, ?string $column, DateRange $range, ?callable $formatter = null): Stat
    {
        $metrics = $this->calculateMetrics($table, $column, $range);
        $value = $formatter ? $formatter($metrics['current']) : $this->formatNumber($metrics['current']);

        return $this->buildStat(
            title: $title,
            value: $value,
            metrics: $metrics,
            chart: $this->getChartData($table, $column, $range->start, $range->end)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateMetrics(string $table, ?string $column, DateRange $range): array
    {
        $current = $column
            ? $this->getSum($table, $column, $range->start, $range->end)
            : $this->getCount($table, $range->start, $range->end);

        $previous = $column
            ? $this->getSum($table, $column, $range->previous()->start, $range->previous()->end)
            : $this->getCount($table, $range->previous()->start, $range->previous()->end);

        $diff = $current - $previous;
        $trend = $this->calculateTrend($diff);

        return compact('current', 'diff', 'trend');
    }

    private function formatNumber(int $number): string
    {
        $number = round(floatval($number) / 100, 2);

        return match (true) {
            $number < 1000 => (string) Number::format($number, 0, locale: config('app.locale')),
            $number < 1000000 => Number::format($number / 1000, 2, locale: config('app.locale')) . 'k',
            default => Number::format($number / 1000000, 2, locale: config('app.locale')) . 'M',
        };
    }

    /**
     * @param  array<string, mixed>  $metrics
     * @param  array<array-key, mixed>  $chart
     */
    private function buildStat(string $title, string $value, array $metrics, array $chart): Stat
    {
        $description = $metrics['current'] > 0
            ? $this->calculatePercentageChange($metrics['current'] - $metrics['diff'], $metrics['diff']) . '% ' . $metrics['trend']['direction']
            : '0%';

        return Stat::make($title, $value)
            ->description($description)
            ->descriptionIcon($metrics['trend']['icon'])
            ->chart($chart)
            ->color($metrics['trend']['color']);
    }

    private function getStartDate(): Carbon
    {
        return ! is_null($this->filters['startDate'] ?? null) ? Carbon::parse($this->filters['startDate']) : now()->subDays(7);
    }

    private function getEndDate(): Carbon
    {
        return ! is_null($this->filters['endDate'] ?? null) ? Carbon::parse($this->filters['endDate']) : now();
    }

    /**
     * @return array<string, string>
     */
    private function calculateTrend(int | float $diff): array
    {
        $isPositive = $diff >= 0;
        $direction = $isPositive ? 'increase' : 'decrease';

        return [
            'direction' => __('widgets/stats-overview.trend.' . $direction),
            'icon' => $isPositive ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down',
            'color' => $isPositive ? 'success' : 'danger',
        ];
    }

    private function calculatePercentageChange(int | float $oldValue, int | float $diffValue): int | float
    {
        if ($oldValue == 0) {
            return $diffValue > 0 ? 100 : 0;
        }

        return round(($diffValue / $oldValue) * 100, 2);
    }

    private function getSum(string $table, string $column, Carbon $startDate, Carbon $endDate): mixed
    {
        $query = OrderResource::getEloquentQuery()->whereBetween('created_at', [$startDate, $endDate]);

        if ($table === 'orders') {
            $query->where('status', '!=', 'cancelled');
        }

        return $query->sum($column);
    }

    private function getCount(string $table, Carbon $startDate, Carbon $endDate): int
    {
        $query = $table === 'orders'
            ? OrderResource::getEloquentQuery()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', '!=', 'cancelled')
            : Customer::whereBetween('created_at', [$startDate, $endDate]);

        return (int) $query::selectRaw('COUNT(*) as count')->value('count');
    }

    /**
     * @return array<array-key, mixed>
     */
    private function getChartData(string $model, ?string $column, Carbon $startDate, Carbon $endDate): array
    {
        if ($model === 'orders') {
            $trend = Trend::query(OrderResource::getEloquentQuery()->where('status', '!=', 'cancelled'))
                ->between(start: $startDate, end: $endDate)
                ->perDay();
        } else {
            $trend = Trend::model(Customer::class)
                ->between(start: $startDate, end: $endDate)
                ->perDay();
        }

        return $column
            ? $trend->sum($column)->map(fn ($value) => $value->aggregate)->toArray()
            : $trend->count()->map(fn ($value) => $value->aggregate)->toArray();
    }
}
