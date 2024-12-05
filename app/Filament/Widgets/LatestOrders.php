<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderResource::getEloquentQuery()->where('status', '!=', 'cancelled')
            )
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->heading(__('widgets/latest-orders.heading'))
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('widgets/latest-orders.column.created_at.label'))
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number')
                    ->label(__('widgets/latest-orders.column.number.label'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('widgets/latest-orders.column.customer.label'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('widgets/latest-orders.column.status.label'))
                    ->badge(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label(__('widgets/latest-orders.column.total_price.label'))
                    ->searchable()
                    ->sortable()
                    ->money('IDR'),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label(__('widgets/latest-orders.action.edit.label'))
                    ->url(fn (Order $record): string => OrderResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
