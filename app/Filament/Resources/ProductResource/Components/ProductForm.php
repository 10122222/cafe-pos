<?php

namespace App\Filament\Resources\ProductResource\Components;

use App\Models\Product;
use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

class ProductForm
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
            static::getNameField(),
            static::getSlugField(),
            static::getDescriptionField(),
        ])->columns();
    }

    protected static function getImagesSection(): Section
    {
        return static::getSection('Images', [
            static::getImagesField(),
        ])->collapsible();
    }

    protected static function getPricingSection(): Section
    {
        return static::getSection('Pricing', [
            static::getPriceField(),
        ]);
    }

    protected static function getStatusSection(): Section
    {
        return static::getSection('Status', [
            static::getVisibilityField(),
        ]);
    }

    protected static function getAssociationsSection(): Section
    {
        return static::getSection('Associations', [
            static::getCategoryField(),
        ]);
    }

    protected static function getNameField(): TextInput
    {
        return TextInput::make('name')
            ->label('Name')
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
            });
    }

    protected static function getSlugField(): TextInput
    {
        return TextInput::make('slug')
            ->label('Slug')
            ->disabled()
            ->dehydrated()
            ->required()
            ->maxLength(255)
            ->unique(Product::class, 'slug', ignoreRecord: true);
    }

    protected static function getDescriptionField(): MarkdownEditor
    {
        return MarkdownEditor::make('description')
            ->label('Description')
            ->disableToolbarButtons(['attachFiles', 'codeBlock', 'table'])
            ->columnSpanFull();
    }

    protected static function getImagesField(): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make('media')
            ->collection('product-images')
            ->conversion('webp')
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->hiddenLabel();
    }

    protected static function getPriceField(): TextInput
    {
        return TextInput::make('price')
            ->label('Price')
            ->numeric()
            ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
            ->required();
    }

    protected static function getVisibilityField(): Toggle
    {
        return Toggle::make('is_visible')
            ->onColor('success')
            ->offColor('danger')
            ->onIcon('heroicon-s-check')
            ->offIcon('heroicon-s-x-mark')
            ->label('Visible')
            ->helperText('Whether the category is visible to customers')
            ->default(true);
    }

    protected static function getCategoryField(): Select
    {
        return Select::make('category_id')
            ->label('Category')
            ->relationship('category', 'name')
            ->searchable()
            ->preload()
            ->required();
    }
}
