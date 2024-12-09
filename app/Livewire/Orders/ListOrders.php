<?php

namespace App\Livewire\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class ListOrders extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * @var array<string, mixed> | null
     */
    #[Url]
    public ?array $tableFilters = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->where('status', '=', OrderStatus::Completed))
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->date(),
                Tables\Columns\TextColumn::make('items_sum_qty')
                    ->label('')
                    ->summarize([
                        Summarizer::make()
                            ->label(__('clusters/pages/report.sales.table.summary.min'))
                            ->using(fn (Builder $query): string => $query->min('total_price'))
                            ->money('IDR', 100),
                        Summarizer::make()
                            ->label(__('clusters/pages/report.sales.table.summary.sum'))
                            ->using(fn (Builder $query) => $query->sum('total_price'))
                            ->money('IDR', 100),
                    ]),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('')
                    ->summarize([
                        Summarizer::make()
                            ->label(__('clusters/pages/report.sales.table.summary.max'))
                            ->using(fn (Builder $query): string => $query->max('total_price'))
                            ->money('IDR', 100),
                        Summarizer::make()
                            ->label(__('clusters/pages/report.sales.table.summary.avg'))
                            ->using(fn (Builder $query) => $query->avg('total_price'))
                            ->money('IDR', 100),
                    ]),
            ])
            ->filters([
                DateRangeFilter::make('created_at')
                    ->label('Date Range')
                    ->defaultThisYear()
                    ->alwaysShowCalendar(false)
                    ->autoApply(),
            ])
            ->hiddenFilterIndicators()
            ->defaultGroup(Group::make('created_at')->date())
            ->groupsOnly();
    }

    public function render(): View
    {
        return view('livewire.orders.list-orders');
    }
}