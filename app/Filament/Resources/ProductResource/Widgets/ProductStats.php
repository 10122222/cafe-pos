<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Product;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class ProductStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListProducts::class;
    }

    protected function getStats(): array
    {
        $totalProducts = $this->getPageTableQuery()->count();
        $favoriteProduct = $this->getPageTableQuery()->withCount('items')->orderBy('items_count', 'desc')->first();
        $averagePrice = round(floatval($this->getPageTableQuery()->avg('price')) / 100, precision: 2);

        return [
            Stat::make('Total products', $totalProducts),
            Stat::make(
                'Favorite product',
                $favoriteProduct instanceof Product ? $favoriteProduct->name : 'N/A'
            ),
            Stat::make(
                'Average price',
                Number::currency($averagePrice, 'IDR', 'id')
            ),
        ];
    }
}
