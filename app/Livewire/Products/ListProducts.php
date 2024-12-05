<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class ListProducts extends Component implements HasForms, HasTable
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
            ->query(
                Product::query()->with('items')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('items_sum_qty')
                    ->label(__('clusters/pages/report.product.table.columns.ordered'))
                    ->sum(
                        ['items' => function (EloquentBuilder $query) {
                            $dateRange = isset($this->tableFilters) ? $this->tableFilters['created_at']['created_at'] : now()->startOfYear()->format('d/m/Y') . ' - ' . now()->endOfYear()->format('d/m/Y');
                            $dateRange = explode(' - ', $dateRange);
                            $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($dateRange[0]))?->startOfDay();
                            $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($dateRange[1]))?->endOfDay();

                            return $query->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate);
                        }],
                        'qty'
                    )
                    ->default(0)
                    ->summarize([
                        Tables\Columns\Summarizers\Summarizer::make()
                            ->label(__('clusters/pages/report.product.table.summary.least'))
                            ->using(
                                fn (Builder $query): string => $query->where('items_sum_qty', '>', 0)
                                    ->where('items_sum_qty', '=', $query->where('items_sum_qty', '>', 0)->min('items_sum_qty'))
                                    ->value('name') ?? '-'
                            ),

                    ]),
                Tables\Columns\TextColumn::make('revenue')
                    ->getStateUsing(fn (Product $product, Table $table) => $product->items->avg('unit_price') * $table->getColumn('items_sum_qty')?->getState())
                    ->money('IDR')
                    ->summarize([
                        Tables\Columns\Summarizers\Summarizer::make()
                            ->label(__('clusters/pages/report.product.table.summary.most'))
                            ->using(
                                fn (Builder $query): string => $query->where('items_sum_qty', '>', 0)
                                    ->where('items_sum_qty', '=', $query->where('items_sum_qty', '>', 0)->max('items_sum_qty'))
                                    ->value('name') ?? '-'
                            ),
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
                    ->autoApply()
                    ->modifyQueryUsing(
                        function (EloquentBuilder $query, ?Carbon $startDate, ?Carbon $endDate, $dateString) {
                            return $query->when(
                                ! empty($dateString),
                                function (EloquentBuilder $query) use ($startDate, $endDate) {
                                    return $query->with('items', function ($itemQuery) use ($startDate, $endDate) {
                                        return $itemQuery->whereBetween('created_at', [$startDate, $endDate]);
                                    });
                                }
                            );
                        }
                    ),
            ])
            ->hiddenFilterIndicators()
            ->paginated(false);
    }

    public function render(): View
    {
        return view('livewire.products.list-products');
    }
}
