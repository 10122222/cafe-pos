<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return OrderResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make(__('resources/order.tabs.all')),
            'new' => Tab::make(__('resources/order.tabs.new'))->query(fn ($query) => $query->where('status', 'new'))->badge(Order::query()->where('status', 'new')::count())->badgeColor('info'),
            'processing' => Tab::make(__('resources/order.tabs.processing'))->query(fn ($query) => $query->where('status', 'processing'))->badge(Order::query()->where('status', 'processing')::count())->badgeColor('warning'),
            'completed' => Tab::make(__('resources/order.tabs.completed'))->query(fn ($query) => $query->where('status', 'completed'))->badge(Order::query()->where('status', 'completed')::count())->badgeColor('success'),
            'cancelled' => Tab::make(__('resources/order.tabs.cancelled'))->query(fn ($query) => $query->where('status', 'cancelled'))->badge(Order::query()->where('status', 'cancelled')::count())->badgeColor('danger'),
        ];
    }
}
