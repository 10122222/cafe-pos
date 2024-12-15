<?php

namespace App\Filament\Resources\ProductResource\Components;

use App\Models\Product;
use Closure;
use Filament\Infolists\Components\Component;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Contracts\Support\Htmlable;

class ProductInfolist
{
    /**
     * @return array<Component>
     */
    public static function getSchema(): array
    {
        return [
            Group::make()
                ->schema([
                    static::getDetailsSection(),
                    static::getImagesSection(),
                    static::getPricingSection(),
                ])
                ->columnSpan(['lg' => 2]),

            Group::make()
                ->schema([
                    static::getStatusSection(),
                    static::getAssociationsSection(),
                    static::getTimestampsSection(),
                ])
                ->columnSpan(['lg' => 1]),
        ];
    }

    /**
     * @param  string|array<Component>|Htmlable|Closure|null  $heading
     * @param  array<Component>|Closure  $schema
     */
    protected static function getSection($heading, $schema): Section
    {
        return Section::make($heading)
            ->schema($schema);
    }

    protected static function getDetailsSection(): Section
    {
        return static::getSection(null, [
            static::getNameEntry(),
            static::getSlugEntry(),
            static::getDescriptionEntry(),
        ])->columns();
    }

    protected static function getImagesSection(): Section
    {
        return static::getSection('Images', [
            static::getImageEntry(),
        ]);
    }

    protected static function getPricingSection(): Section
    {
        return static::getSection('Pricing', [
            static::getPriceEntry(),
        ]);
    }

    protected static function getStatusSection(): Section
    {
        return static::getSection('Status', [
            static::getVisibilityEntry(),
        ]);
    }

    protected static function getAssociationsSection(): Section
    {
        return static::getSection('Associations', [
            static::getCategoryEntry(),
        ]);
    }

    protected static function getTimestampsSection(): Section
    {
        return static::getSection(null, [
            static::getCreatedAtEntry(),
            static::getUpdatedAtEntry(),
        ]);
    }

    protected static function getNameEntry(): TextEntry
    {
        return TextEntry::make('name');
    }

    protected static function getSlugEntry(): TextEntry
    {
        return TextEntry::make('slug');
    }

    protected static function getDescriptionEntry(): TextEntry
    {
        return TextEntry::make('description')
            ->markdown()
            ->placeholder(__('resources/product.no_description'))
            ->columnSpanFull();
    }

    protected static function getImageEntry(): SpatieMediaLibraryImageEntry
    {
        return SpatieMediaLibraryImageEntry::make('media')
            ->collection('product-images')
            ->conversion('webp')
            ->hiddenLabel()
            ->placeholder(__('resources/product.no_images'))
            ->checkFileExistence(false)
            ->extraImgAttributes(fn (Product $record) => [
                'alt' => 'Product image of ' . $record->name,
                'loading' => 'lazy',
            ]);
    }

    protected static function getPriceEntry(): TextEntry
    {
        return TextEntry::make('price')
            ->money('IDR');
    }

    protected static function getVisibilityEntry(): IconEntry
    {
        return IconEntry::make('is_visible')
            ->label('Visibility')
            ->icon(fn (string $state): string => match ($state) {
                '1' => 'heroicon-o-check-circle',
                default => 'heroicon-o-x-circle',
            });
    }

    protected static function getCategoryEntry(): TextEntry
    {
        return TextEntry::make('category.name');
    }

    protected static function getCreatedAtEntry(): TextEntry
    {
        return TextEntry::make('created_at')
            ->since()
            ->dateTimeTooltip(timezone: 'Asia/Jakarta');
    }

    protected static function getUpdatedAtEntry(): TextEntry
    {
        return TextEntry::make('updated_at')
            ->since()
            ->dateTimeTooltip(timezone: 'Asia/Jakarta');
    }
}
