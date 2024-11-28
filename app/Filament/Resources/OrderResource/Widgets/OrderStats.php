<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Models\Order;
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

    protected function getStats(): array
    {
        $totalOrders = $this->getPageTableQuery()->count();
        $orderData = Trend::model(Order::class)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        $openOrders = $this->getPageTableQuery()->whereIn('status', ['open', 'processing'])->count();

        $averagePrice = round(floatval($this->getPageTableQuery()->avg('total_price')) / 100, precision: 2);

        return [
            Stat::make(__('resources/order.stat.orders'), $totalOrders)
                ->chart(
                    $orderData
                        ->map(fn (TrendValue $value) => $value->aggregate)
                        ->toArray()
                ),
            Stat::make(__('resources/order.stat.open_orders'), $openOrders),
            Stat::make(__('resources/order.stat.avg_price'), Number::currency($averagePrice, 'IDR', config('app.locale'))),
        ];
    }
}
