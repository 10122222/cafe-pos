<?php

namespace App\Filament\Resources\OrderResource\Components;

use App\Enums\OrderStatus;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;

class OrderTable
{
    /** @return array<Column> */
    public static function getColumns(): array
    {
        return [
            static::getNumberColumn(),
            static::getCustomerNameColumn(),
            static::getStatusColumn(),
            static::getTotalPriceColumn(),
            static::getCreatedAtColumn(),
        ];
    }

    /** @return array<BaseFilter> */
    public static function getFilters(): array
    {
        return [
            TrashedFilter::make(),
            static::getCreatedAtFilter(),
            SelectFilter::make('status')
                ->options(OrderStatus::class)
                ->multiple()
                ->label(__('resources/order.status')),
        ];
    }

    /** @return array<ActionGroup> */
    public static function getActions(): array
    {
        return [
            ActionGroup::make([
                EditAction::make(),
                static::getPdfAction(),
            ]),
        ];
    }

    /** @return array<BulkActionGroup> */
    public static function getBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
    }

    /** @return array<Group> */
    public static function getGroups(): array
    {
        return [
            Group::make('created_at')
                ->label(__('resources/order.date'))
                ->date()
                ->titlePrefixedWithLabel(false)
                ->collapsible(),
        ];
    }

    protected static function getNumberColumn(): TextColumn
    {
        return TextColumn::make('number')
            ->label(__('resources/order.number'))
            ->searchable()
            ->sortable();
    }

    protected static function getCustomerNameColumn(): TextColumn
    {
        return TextColumn::make('customer.name')
            ->label(__('resources/order.customer'))
            ->searchable()
            ->sortable()
            ->toggleable();
    }

    protected static function getStatusColumn(): TextColumn
    {
        return TextColumn::make('status')
            ->badge()
            ->label(__('resources/order.status'));
    }

    protected static function getTotalPriceColumn(): TextColumn
    {
        return TextColumn::make('total_price')
            ->label(__('resources/order.total'))
            ->searchable()
            ->sortable()
            ->summarize([
                Sum::make()
                    ->money('IDR', 100),
            ])
            ->money('IDR');
    }

    protected static function getCreatedAtColumn(): TextColumn
    {
        return TextColumn::make('created_at')
            ->label(__('resources/order.date'))
            ->date(timezone: 'Asia/Jakarta')
            ->dateTimeTooltip(timezone: 'Asia/Jakarta')
            ->sortable()
            ->toggleable();
    }

    protected static function getCreatedAtFilter(): Filter
    {
        return Filter::make('created_at')
            ->form([
                DatePicker::make('created_from')
                    ->label(__('resources/order.filters.created_from'))
                    ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                DatePicker::make('created_until')
                    ->label(__('resources/order.filters.created_until'))
                    ->placeholder(fn ($state): string => now()->format('M d, Y')),
            ])
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when(
                        $data['created_from'] ?? null,
                        fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                    )
                    ->when(
                        $data['created_until'] ?? null,
                        fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                    );
            })
            ->indicateUsing(function (array $data): array {
                $indicators = [];
                if ($data['created_from'] ?? null) {
                    $indicators['created_from'] = __('resources/order.filters.created_from') . ' ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                }
                if ($data['created_until'] ?? null) {
                    $indicators['created_until'] = __('resources/order.filters.created_until') . ' ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                }

                return $indicators;
            });
    }

    public static function getPdfAction(): Action
    {
        return Action::make('pdf')
            ->label(__('resources/order.actions.pdf'))
            ->color('success')
            ->icon('heroicon-o-arrow-down-tray')
            ->action(function (Order $record) {
                $record = $record->load('customer', 'items.product', 'payment');
                $filename = 'INVOICE-' . $record->number . '-' . $record->created_at?->format('d-m-Y') . '.pdf';

                return response()->streamDownload(function () use ($record) {
                    echo Pdf::loadHtml(
                        Blade::render('invoice', ['order' => $record])
                    )->stream();
                }, $filename);
            })
            ->hidden(fn (Order $record): bool => $record->status !== OrderStatus::Completed);
    }
}
