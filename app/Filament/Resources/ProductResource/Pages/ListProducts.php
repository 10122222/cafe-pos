<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListProducts extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = ProductResource::class;

    protected function getHeaderWidgets(): array
    {
        return ProductResource::getWidgets();
    }

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(ProductImporter::class)
                    ->label('Import')
                    ->tooltip('Import products'),
                Tables\Actions\ExportAction::make()
                    ->exporter(ProductExporter::class)
                    ->label('Export')
                    ->tooltip('Export products'),
                Tables\Actions\CreateAction::make(),
            ])
            ->columns([
                Tables\Columns\Layout\Split::make([
                    static::getImagesColumn(),
                    Tables\Columns\Layout\Stack::make([
                        static::getCategoryColumn(),
                        static::getNameColumn(),
                        static::getPriceColumn(),
                    ]),
                ]),
            ])
            ->contentGrid([
                'sm' => 2,
                'xl' => 3,
            ])
            ->filters([
                static::getVisibilityFilter(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Product deleted')
                            ->body('The product has been deleted successfully.')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->selectCurrentPageOnly()
            ->groups([
                Tables\Grouping\Group::make('category.name')
                    ->label('Category'),
            ])
            ->deferLoading();
    }

    public static function getImagesColumn(): SpatieMediaLibraryImageColumn
    {
        return SpatieMediaLibraryImageColumn::make('product-image')
            ->defaultImageUrl(url('https://placehold.co/64x64.webp?text=No+Image'))
            ->size(64)
            ->square()
            ->grow(false)
            ->label('Image')
            ->collection('product-images')
            ->conversion('webp')
            ->checkFileExistence(false)
            ->extraImgAttributes(fn (Product $record) => [
                'alt' => 'Product image of ' . $record->name,
                'loading' => 'lazy',
            ]);
    }

    public static function getNameColumn(): TextColumn
    {
        return TextColumn::make('name')
            ->label('Name')
            ->wrap()
            ->searchable()
            ->sortable();
    }

    public static function getCategoryColumn(): TextColumn
    {
        return TextColumn::make('category.name')
            ->label('Category')
            ->placeholder('-')
            ->searchable()
            ->sortable()
            ->hidden(fn (Table $table) => $table->getGrouping() !== null);
    }

    public static function getPriceColumn(): TextColumn
    {
        return TextColumn::make('price')
            ->label('Price')
            ->sortable()
            ->money('IDR');
    }

    public static function getVisibilityFilter(): Tables\Filters\BaseFilter
    {
        return Tables\Filters\TernaryFilter::make('is_visible')
            ->label('Visibility')
            ->placeholder('All products')
            ->trueLabel('Visible products')
            ->falseLabel('Not visible products');
    }
}
