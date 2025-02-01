<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Number;

class OrderStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListOrders::class;
    }

    protected function getCurrentTabStatus(): ?string
    {
        return $this->getPropertyValue('activeTab');
    }

    protected function getStats(): array
    {
        $baseQuery = $this->getPageTableQuery();
        $baseTrendQuery = OrderResource::getEloquentQuery();

        if ($status = $this->getCurrentTabStatus()) {
            $baseQuery->where('status', $status);
            $baseTrendQuery->where('status', $status);
        }

        $totalOrders = $baseQuery->count();

        $trendQuery = Trend::query($baseTrendQuery)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth();

        $orderData = $trendQuery->count();

        $openOrders = $baseQuery->clone()
            ->whereIn('status', ['new', 'processing'])
            ->count();

        $averagePrice = round(floatval($baseQuery->avg('total_price')) / 100, precision: 2);

        return [
            Stat::make('Total orders', $totalOrders)
                ->chart(
                    $orderData
                        ->map(fn (TrendValue $value) => $value->aggregate)
                        ->toArray()
                ),
            Stat::make('Open orders', $openOrders),
            Stat::make('Average price', Number::currency($averagePrice, 'IDR', config('app.locale'))),
        ];
    }
}
