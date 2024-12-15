<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Components\ProductForm;
use App\Filament\Resources\ProductResource\Components\ProductInfolist;
use App\Filament\Resources\ProductResource\Components\ProductTable;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\Widgets\ProductStats;
use App\Models\Product;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
            ->schema(ProductForm::getSchema())
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(ProductTable::getColumns())
            ->filters(ProductTable::getFilters(), ProductTable::getFiltersLayout())
            ->deferFilters()
            ->actions(ProductTable::getActions())
            ->bulkActions(ProductTable::getBulkActions())
            ->groups(ProductTable::getGroups())
            ->defaultSort('category.name');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema(ProductInfolist::getSchema())
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
