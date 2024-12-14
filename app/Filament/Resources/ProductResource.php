<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\Widgets\ProductStats;
use App\Models\Product;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Infolists\Components\Group as InfolistsGroup;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section as InfolistsSection;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Grouping\Group as TablesGroup;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Product::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?int $navigationSort = 1;

    /** @return string[] */
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
                Group::make()
                    ->schema([
                        static::getDetailsSectionFormComponent(),
                        static::getImagesSectionFormComponent(),
                        static::getPricingSectionFormComponent(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        static::getStatusSectionFormComponent(),
                        static::getAssociationsSectionFormComponent(),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('product-image')
                    ->defaultImageUrl(url('https://placehold.co/40x40.webp?text=No+Image'))
                    ->square()
                    ->grow(false)
                    ->label(__('resources/product.image'))
                    ->collection('product-images')
                    ->conversion('webp')
                    ->checkFileExistence(false)
                    ->extraImgAttributes(fn (Product $record) => [
                        'alt' => 'Product image of ' . $record->name,
                        'loading' => 'lazy',
                    ]),

                TextColumn::make('name')
                    ->label(__('resources/product.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label(__('resources/product.category'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                ToggleColumn::make('is_visible')
                    ->label(__('resources/product.visibility'))
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('price')
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
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->deferFilters()
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                TablesGroup::make('category.name')
                    ->label(__('resources/product.category'))
                    ->collapsible(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistsGroup::make()
                    ->schema([
                        InfolistsSection::make()
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('resources/product.name')),
                                TextEntry::make('description')
                                    ->markdown()
                                    ->label(__('resources/product.description'))
                                    ->placeholder(__('resources/product.no_description')),
                            ]),

                        InfolistsSection::make(__('resources/product.images'))
                            ->schema([
                                SpatieMediaLibraryImageEntry::make('media')
                                    ->collection('product-images')
                                    ->conversion('webp')
                                    ->hiddenLabel()
                                    ->placeholder(__('resources/product.no_images'))
                                    ->checkFileExistence(false)
                                    ->extraImgAttributes(fn (Product $record) => [
                                        'alt' => 'Product image of ' . $record->name,
                                        'loading' => 'lazy',
                                    ]),
                            ]),
                        InfolistsSection::make(__('resources/product.pricing'))
                            ->schema([
                                TextEntry::make('price')
                                    ->label(__('resources/product.price'))
                                    ->money('IDR'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                InfolistsGroup::make()
                    ->schema([
                        InfolistsSection::make(__('resources/product.status'))
                            ->schema([
                                IconEntry::make('is_visible')
                                    ->label(__('resources/product.visibility'))
                                    ->icon(fn (string $state): string => match ($state) {
                                        '1' => 'heroicon-o-check-circle',
                                        default => 'heroicon-o-x-circle',
                                    }),
                            ]),

                        InfolistsSection::make(__('resources/product.associations'))
                            ->schema([
                                TextEntry::make('category.name')
                                    ->label(__('resources/product.category')),
                            ]),
                        InfolistsSection::make()
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label(__('resources/product.created_at'))
                                    ->since()
                                    ->dateTimeTooltip(timezone: 'Asia/Jakarta'),

                                TextEntry::make('updated_at')
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

    /**
     * @param  string|array<Component>|Htmlable|Closure|null  $heading
     * @param  array<Component>|Closure  $schema
     */
    protected static function getSectionFormComponent($heading, $schema): Section
    {
        return Section::make($heading)
            ->schema($schema);
    }

    protected static function getDetailsSectionFormComponent(): Component
    {
        return Section::make()
            ->schema([
                TextInput::make('name')
                    ->label(__('resources/product.name'))
                    ->unique('products', 'name', ignoreRecord: true)
                    ->required()
                    ->maxLength(255)
                    ->autofocus(fn (string $operation) => $operation === 'create')
                    ->lazy()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if ($state === null) {
                            return;
                        }

                        $set('slug', Str::slug($state));
                    }),

                TextInput::make('slug')
                    ->label(__('resources/product.slug'))
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->maxLength(255)
                    ->unique(Product::class, 'slug', ignoreRecord: true),

                MarkdownEditor::make('description')
                    ->label(__('resources/product.description'))
                    ->columnSpanFull(),
            ])
            ->columns();
    }

    protected static function getImagesSectionFormComponent(): Component
    {
        return static::getSectionFormComponent(__('resources/product.images'), [
            static::getImagesFormComponent(),
        ])->collapsible();
    }

    protected static function getImagesFormComponent(): Component
    {
        return SpatieMediaLibraryFileUpload::make('media')
            ->collection('product-images')
            ->conversion('webp')
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->hiddenLabel();
    }

    protected static function getPricingSectionFormComponent(): Component
    {
        return static::getSectionFormComponent(__('resources/product.pricing'), [
            static::getPriceFormComponent(),
        ]);
    }

    protected static function getPriceFormComponent(): Component
    {
        return TextInput::make('price')
            ->label(__('resources/product.price'))
            ->numeric()
            ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
            ->required();
    }

    protected static function getStatusSectionFormComponent(): Component
    {
        return static::getSectionFormComponent(__('resources/product.status'), [
            static::getVisibilityFormComponent(),
        ]);
    }

    protected static function getVisibilityFormComponent(): Component
    {
        return Toggle::make('is_visible')
            ->label(__('resources/product.visibility'))
            ->helperText(__('resources/product.visibility_help'))
            ->default(true);
    }

    protected static function getAssociationsSectionFormComponent(): Component
    {
        return static::getSectionFormComponent(__('resources/product.associations'), [
            static::getCategoryFormComponent(),
        ]);
    }

    protected static function getCategoryFormComponent(): Component
    {
        return Select::make('category_id')
            ->label(__('resources/product.category'))
            ->relationship('category', 'name')
            ->searchable()
            ->preload()
            ->required();
    }
}
