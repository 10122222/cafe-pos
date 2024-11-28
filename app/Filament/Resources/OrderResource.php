<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\Widgets\OrderStats;
use App\Models\Order;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;

class OrderResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Order::class;

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 2;

    /** @return array<string> */
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'restore',
            'restore_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('resources/order.details'))
                            ->schema(static::getDetailsFormSchema()),

                        Forms\Components\Section::make(__('resources/order.items'))
                            ->headerActions([
                                Action::make('reset')
                                    ->label(__('resources/order.actions.reset'))
                                    ->modalHeading(__('resources/order.messages.reset_confirmation'))
                                    ->modalDescription(__('resources/order.messages.reset_description'))
                                    ->requiresConfirmation()
                                    ->color('danger')
                                    ->action(fn (Forms\Set $set) => $set(
                                        'items',
                                        Product::query()::whereIsVisible(true)->whereHas('category', fn ($query) => $query::whereIsVisible(true))->get()
                                            ->map(fn (Product $product) => [
                                                'product_id' => $product->id,
                                                'qty' => 0,
                                                'unit_price' => $product->price,
                                            ])
                                            ->toArray()
                                    ))
                                    ->disabled(fn (?Order $record) => $record?->status !== OrderStatus::New),
                            ])
                            ->schema([
                                static::getItemsRepeater(),
                            ]),
                    ])
                    ->columnSpan(['lg' => fn (?Order $record) => $record === null ? 3 : 2]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Placeholder::make('created_at')
                                    ->label(__('resources/order.created_at'))
                                    ->content(fn (Order $record): ?string => $record->created_at?->setTimezone('Asia/Jakarta')->diffForHumans()),

                                Forms\Components\Placeholder::make('updated_at')
                                    ->label(__('resources/order.updated_at'))
                                    ->content(fn (Order $record): ?string => $record->updated_at?->setTimezone('Asia/Jakarta')->diffForHumans()),
                            ])
                            ->columnSpan(['lg' => 1])
                            ->hidden(fn (?Order $record) => $record === null),

                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Placeholder::make('total_price')
                                    ->label(__('resources/order.total'))
                                    ->content(fn (Order $record): ?string => $record->total_price ? 'Rp ' . number_format($record->total_price, 2, ',', '.') : null),

                                static::getPaymentFormSchema(),
                            ])
                            ->columnSpan(['lg' => 1])
                            ->hidden(fn (?Order $record) => $record === null),
                    ])
                    ->compact()
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Order $record) => $record === null),
            ])
            ->disabled(fn (?Order $record): bool => $record?->status === OrderStatus::Completed || $record?->status === OrderStatus::Cancelled)
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label(__('resources/order.number'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('resources/order.customer'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('resources/order.status'))
                    ->badge(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label(__('resources/order.total'))
                    ->searchable()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('IDR', 100),
                    ])
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('resources/order.date'))
                    ->date(timezone: 'Asia/Jakarta')
                    ->dateTimeTooltip(timezone: 'Asia/Jakarta')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('resources/order.filters.created_from'))
                            ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('created_until')
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
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->options(OrderStatus::class)
                    ->multiple()
                    ->label(__('resources/order.status')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('pdf')
                        ->label(__('resources/order.actions.pdf'))
                        ->color('success')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Order $record) {
                            return response()->streamDownload(function () use ($record) {
                                echo Pdf::loadHtml(
                                    Blade::render('pdf', ['record' => $record])
                                )->stream();
                            }, $record->number . '.pdf');
                        })
                        ->hidden(fn (Order $record): bool => $record->status !== OrderStatus::Completed),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('created_at')
                    ->label(__('resources/order.date'))
                    ->date()
                    ->collapsible(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            OrderStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    /** @return Builder<Order> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'customer.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Order $record */

        return [
            'Customer' => optional($record->customer)->name,
        ];
    }

    /** @return Builder<Order> */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['customer', 'items']);
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::where('status', 'new')->count();
    }

    /** @return Forms\Components\Component[] */
    public static function getDetailsFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('number')
                ->label(__('resources/order.number'))
                ->default('OR-' . random_int(100000, 999999))
                ->disabled()
                ->dehydrated()
                ->required()
                ->maxLength(32)
                ->unique(Order::class, 'number', ignoreRecord: true),

            Forms\Components\Select::make('customer_id')
                ->label(__('resources/order.customer'))
                ->relationship('customer', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')
                        ->label(__('resources/order.customer.name'))
                        ->required()
                        ->maxLength(255),
                ])
                ->createOptionAction(function (Action $action) {
                    return $action
                        ->modalHeading(__('resources/order.actions.create_customer'))
                        ->modalSubmitActionLabel(__('resources/order.actions.create_customer'))
                        ->modalWidth('lg');
                })
                ->autofocus(fn (string $operation) => $operation === 'create'),

            Forms\Components\ToggleButtons::make('status')
                ->label(__('resources/order.status'))
                ->inline()
                ->options(OrderStatus::class)
                ->required(),

            Forms\Components\MarkdownEditor::make('notes')
                ->label(__('resources/order.notes'))
                ->columnSpanFull(),
        ];
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('items')
            ->relationship('items')
            ->schema([
                Forms\Components\Hidden::make('product_id'),

                Forms\Components\Placeholder::make('product_name')
                    ->label(__('resources/order.item.product'))
                    ->content(function (Get $get): string {
                        $productId = $get('product_id');
                        $product = Product::find($productId);

                        return $product?->name ?? '';
                    })
                    ->columnSpan([
                        'md' => 5,
                    ])
                    ->extraAttributes(['class' => 'h-9 flex items-center']),

                Forms\Components\TextInput::make('qty')
                    ->label(__('resources/order.item.quantity'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(999)
                    ->default(0)
                    ->columnSpan([
                        'md' => 2,
                    ])
                    ->required(),

                Forms\Components\TextInput::make('unit_price')
                    ->label(__('resources/order.item.unit_price'))
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->required()
                    ->columnSpan([
                        'md' => 3,
                    ]),
            ])
            ->extraItemActions([
                Action::make('openProduct')
                    ->tooltip(__('resources/order.item.open_product'))
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(function (array $arguments, Repeater $component): ?string {
                        $itemData = $component->getRawItemState($arguments['item']);

                        $product = Product::find($itemData['product_id']);

                        if (! $product) {
                            return null;
                        }

                        return ProductResource::getUrl('view', ['record' => $product]);
                    }, shouldOpenInNewTab: true)
                    ->hidden(fn (array $arguments, Repeater $component): bool => blank($component->getRawItemState($arguments['item'])['product_id'])),
            ])
            ->mutateRelationshipDataBeforeFillUsing(function (array $data): ?array {
                return $data['qty'] > 0 ? $data : null;
            })
            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): ?array {
                return $data['qty'] > 0 ? $data : null;
            })
            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): ?array {
                return $data['qty'] > 0 ? $data : null;
            })
            ->formatStateUsing(
                fn (?array $state): array => Product::query()::whereIsVisible(true)->whereHas('category', fn ($query) => $query::whereIsVisible(true))->get()
                    ->map(function (Product $product) use ($state) {
                        $existingItem = collect($state)->first(function ($item) use ($product) {
                            return $item['product_id'] === $product->id;
                        });

                        return [
                            'product_id' => $product->id,
                            'qty' => $existingItem['qty'] ?? 0,
                            'unit_price' => $existingItem['unit_price'] ?? $product->price,
                        ];
                    })
                    ->toArray()
            )
            ->minItems(1)
            ->addable(false)
            ->deletable(false)
            ->reorderable(false)
            ->hiddenLabel()
            ->columns([
                'md' => 10,
            ])
            ->required();
    }

    public static function getPaymentFormSchema(): Repeater
    {
        return Repeater::make('payment')
            ->relationship()
            ->simple(
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('Rp')
                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                    ->required()
                    ->step(1000)
                    ->minValue(fn ($state, Forms\Get $get) => $get('../../total_price') ?? 0)
                    ->default(fn ($state, Forms\Get $get) => $get('../../total_price') ?? 0)
                    ->columnSpanFull()
                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
                        $totalPrice = $get('../../total_price') ?? 0;
                        if (! $state && ! $totalPrice) {
                            return;
                        }

                        if ($state >= $totalPrice) {
                            $set('../../status', OrderStatus::Completed);
                        }
                    })
            )
            ->deletable(false)
            ->maxItems(1)
            ->addActionLabel(__('resources/order.actions.add_payment'))
            ->hidden(fn (?Order $record): bool => $record?->status === OrderStatus::New)
            ->label(__('resources/order.cash'))
            ->hiddenLabel(fn (?array $state) => empty($state));
    }

    public static function getModelLabel(): string
    {
        return __('resources/order.single');
    }

    public static function getModelLabelPlural(): string
    {
        return __('resources/order.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('resources/order.nav.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('resources/order.plural');
    }
}
