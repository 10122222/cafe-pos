<?php

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('category')
                ->relationship()
                ->label('Category')
                ->example(__('resources/product.import.examples.category')),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->label('Name')
                ->examples(['Product A', 'Product B']),
            ImportColumn::make('slug')
                ->rules(['max:255'])
                ->label('Slug')
                ->examples(['product-a', 'product-b']),
            ImportColumn::make('description')
                ->label('Description')
                ->examples(['This is the description for Product A', 'This is the description for Product B']),
            ImportColumn::make('is_visible')
                ->requiredMapping()
                ->boolean()
                ->rules(['required', 'boolean'])
                ->label('Visibility')
                ->examples(['yes', 'no']),
            ImportColumn::make('price')
                ->numeric()
                ->rules(['integer'])
                ->label('Price')
                ->example(__('resources/product.import.examples.price')),
        ];
    }

    public function resolveRecord(): ?Product
    {
        return Product::firstOrNew([
            'slug' => $this->data['slug'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
