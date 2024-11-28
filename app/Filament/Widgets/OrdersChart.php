<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Contracts\Support\Htmlable;

class OrdersChart extends ChartWidget
{
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $data = Trend::query(OrderResource::getEloquentQuery()->where('status', '!=', 'cancelled'))
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count()
            ->map(fn (TrendValue $value) => $value->aggregate)
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => __('widgets/orders-chart.datasets.label'),
                    'data' => $data,
                    'fill' => 'start',
                ],
            ],
            'labels' => __('widgets/orders-chart.labels'),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): string | Htmlable | null
    {
        return __('widgets/orders-chart.heading');
    }
}
