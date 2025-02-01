<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Exports\CategoryExporter;
use App\Filament\Imports\CategoryImporter;
use App\Filament\Resources\CategoryResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\ImportAction::make()
                    ->importer(CategoryImporter::class)
                    ->label('Import')
                    ->tooltip('Import categories'),
                Tables\Actions\ExportAction::make()
                    ->exporter(CategoryExporter::class)
                    ->label('Export')
                    ->tooltip('Export categories'),
                Tables\Actions\CreateAction::make(),
            ])
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('name')
                        ->label('Name')
                        ->grow(false)
                        ->searchable()
                        ->sortable(),
                    Tables\Columns\TextColumn::make('products_count')
                        ->label('')
                        ->tooltip('Total products')
                        ->badge()
                        ->counts('products'),
                ]),
            ])
            ->contentGrid([
                'sm' => 2,
                'xl' => 3,
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_visible')
                    ->label('Visibility')
                    ->options([
                        1 => 'Visible',
                        0 => 'Invisible',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Category deleted')
                            ->body('The category has been deleted successfully.')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->selectCurrentPageOnly()
            ->deferLoading();
    }
}
