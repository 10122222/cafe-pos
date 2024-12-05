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
                Tables\Columns\TextColumn::make('number'),
                Tables\Columns\TextColumn::make('items_sum_qty')
                    ->label(__('clusters/pages/report.product.table.columns.ordered'))
                    ->sum('items', 'qty')
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
                    ->label(__('clusters/pages/report.sales.table.columns.total_price'))
                    ->money('IDR')
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
                    ->ranges([
                        'Today' => [now()->startOfDay(), now()->endOfDay()],
                        'Yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
                        'This Week' => [now()->startOfWeek(), now()->endOfWeek()],
                        'Last Week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
                        'This Month' => [now()->startOfMonth(), now()->endOfMonth()],
                        'Last Month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
                        'Last 3 Months' => [now()->subMonths(3)->startOfMonth(), now()->endOfMonth()],
                        'Last 6 Months' => [now()->subMonths(6)->startOfMonth(), now()->endOfMonth()],
                        'This Year' => [now()->startOfYear(), now()->endOfYear()],
                        'Last Year' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
                    ])
                    ->disableClear()
                    ->disableCustomRange()
                    ->autoApply(),
            ])
            ->hiddenFilterIndicators()
            ->paginated(false);
    }

    public function render(): View
    {
        return view('livewire.orders.list-orders');
    }
}
