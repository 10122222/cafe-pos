<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Facades\FilamentView;

use function Filament\Support\is_app_url;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Back')
                ->action('goBack')
                ->color('gray'),
            Actions\EditAction::make(),
        ];
    }

    public function goBack(): void
    {
        $this->redirect($this->getRedirectUrl(), navigate: FilamentView::hasSpaMode() && is_app_url($this->getRedirectUrl()));
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Group::make()
                    ->schema([
                        Infolists\Components\Section::make()
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Name'),
                                Infolists\Components\TextEntry::make('slug')
                                    ->label('Slug'),
                                Infolists\Components\TextEntry::make('description')
                                    ->label('Description')
                                    ->markdown()
                                    ->placeholder('No description provided.')
                                    ->columnSpanFull(),
                            ])
                            ->columns(),

                        Infolists\Components\Section::make('Images')
                            ->schema([
                                Infolists\Components\SpatieMediaLibraryImageEntry::make('media')
                                    ->collection('product-images')
                                    ->conversion('webp')
                                    ->hiddenLabel()
                                    ->placeholder('No image provided.')
                                    ->checkFileExistence(false)
                                    ->extraImgAttributes(fn (Product $record) => [
                                        'alt' => 'Product image of ' . $record->name,
                                        'loading' => 'lazy',
                                    ]),
                            ]),

                        Infolists\Components\Section::make('Pricing')
                            ->schema([
                                Infolists\Components\TextEntry::make('price')
                                    ->label('Price')
                                    ->money('IDR'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Infolists\Components\Group::make()
                    ->schema([
                        Infolists\Components\Section::make('Status')
                            ->schema([
                                Infolists\Components\IconEntry::make('is_visible')
                                    ->label('Visible to customers'),
                            ]),

                        Infolists\Components\Section::make('Associations')
                            ->schema([
                                Infolists\Components\TextEntry::make('category.name')
                                    ->label('Category'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
