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
        $statuses = [
            'new' => 'info',
            'processing' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];

        $tabs = [
            null => Tab::make(__('resources/order.tabs.all')),
        ];

        foreach ($statuses as $status => $color) {
            $tabs[$status] = Tab::make(__('resources/order.tabs.' . $status))
                ->query(fn ($query) => $query->where('status', $status))
                ->badge(Order::query()->where('status', $status)->count())
                ->badgeColor($color);
        }

        return $tabs;
    }
}
