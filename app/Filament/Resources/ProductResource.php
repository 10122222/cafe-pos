<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\Widgets\ProductStats;
use App\Models\Product;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Product::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?int $navigationSort = 1;

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
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('resources/product.name'))
                                    ->unique('products', 'name', ignoreRecord: true)
                                    ->required()
                                    ->maxLength(255)
                                    ->autofocus(fn (string $operation) => $operation === 'create')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                        if ($old === null || $state === null) {
                                            return;
                                        }

                                        if (($get('slug') ?? '') !== Str::slug($old)) {
                                            return;
                                        }

                                        $set('slug', Str::slug($state));
                                    }),

                                Forms\Components\TextInput::make('slug')
                                    ->label(__('resources/product.slug'))
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Product::class, 'slug', ignoreRecord: true),

                                Forms\Components\MarkdownEditor::make('description')
                                    ->label(__('resources/product.description'))
                                    ->columnSpanFull(),
                            ])
                            ->columns(),

                        Forms\Components\Section::make(__('resources/product.images'))
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('media')
                                    ->moveFiles()
                                    ->collection('product-images')
                                    ->multiple()
                                    ->maxFiles(5)
                                    ->reorderable()
                                    ->acceptedFileTypes(['image/jpeg'])
                                    ->hiddenLabel(),
                            ])
                            ->collapsible(),

                        Forms\Components\Section::make(__('resources/product.pricing'))
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label(__('resources/product.price'))
                                    ->numeric()
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->required(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('resources/product.status'))
                            ->schema([
                                Forms\Components\Toggle::make('is_visible')
                                    ->label(__('resources/product.visibility'))
                                    ->helperText(__('resources/product.visibility_help'))
                                    ->default(true),
                            ]),

                        Forms\Components\Section::make(__('resources/product.associations'))
                            ->schema([
                                Forms\Components\Select::make('category_id')
                                    ->label(__('resources/product.category'))
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('product-image')
                    ->label(__('resources/product.image'))
                    ->collection('product-images')
                    ->conversion('thumb'),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('resources/product.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('resources/product.category'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('resources/product.visibility'))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('resources/product.price'))
                    ->searchable()
                    ->sortable()
                    ->money('IDR'),
            ])
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('name')
                            ->label(__('resources/product.name')),
                        TextConstraint::make('description')
                            ->label(__('resources/product.description')),
                        NumberConstraint::make('price')
                            ->label(__('resources/product.price'))
                            ->icon('heroicon-m-currency-dollar'),
                        BooleanConstraint::make('is_visible')
                            ->label(__('resources/product.visibility')),
                    ])
                    ->constraintPickerColumns(),
            ], layout: Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->deferFilters()
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('category.name')
                    ->label(__('resources/product.category'))
                    ->collapsible(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Group::make()
                    ->schema([
                        Infolists\Components\Section::make()
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label(__('resources/product.name')),
                                Infolists\Components\TextEntry::make('description')
                                    ->markdown()
                                    ->label(__('resources/product.description'))
                                    ->placeholder(__('resources/product.no_description')),
                            ]),

                        Infolists\Components\Section::make(__('resources/product.images'))
                            ->schema([
                                SpatieMediaLibraryImageEntry::make('media')
                                    ->collection('product-images')
                                    ->hiddenLabel()
                                    ->placeholder(__('resources/product.no_images')),
                            ]),
                        Infolists\Components\Section::make(__('resources/product.pricing'))
                            ->schema([
                                Infolists\Components\TextEntry::make('price')
                                    ->label(__('resources/product.price'))
                                    ->money('IDR'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Infolists\Components\Group::make()
                    ->schema([
                        Infolists\Components\Section::make(__('resources/product.status'))
                            ->schema([
                                Infolists\Components\IconEntry::make('is_visible')
                                    ->label(__('resources/product.visibility'))
                                    ->icon(fn (string $state): string => match ($state) {
                                        '1' => 'heroicon-o-check-circle',
                                        default => 'heroicon-o-x-circle',
                                    }),
                            ]),

                        Infolists\Components\Section::make(__('resources/product.associations'))
                            ->schema([
                                Infolists\Components\TextEntry::make('category.name')
                                    ->label(__('resources/product.category')),
                            ]),
                        Infolists\Components\Section::make()
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label(__('resources/product.created_at'))
                                    ->since()
                                    ->dateTimeTooltip(timezone: 'Asia/Jakarta'),

                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label(__('resources/product.updated_at'))
                                    ->since()
                                    ->dateTimeTooltip(timezone: 'Asia/Jakarta'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
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
            ProductStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'category.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Product $record */

        return [
            'Category' => optional($record->category)->name,
        ];
    }

    /** @return Builder<Product> */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['category']);
    }

    public static function getModelLabel(): string
    {
        return __('resources/product.single');
    }

    public static function getModelLabelPlural(): string
    {
        return __('resources/product.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('resources/product.nav.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('resources/product.plural');
    }
}
