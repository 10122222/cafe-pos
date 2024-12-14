<?php

namespace App\Livewire;

use App\Models\Product;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Home extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->where('is_visible', true))
            ->heading('List of our categories and products')
            ->headerActions([
                static::loginAction(),
            ])
            ->columns([
                Split::make([
                    SpatieMediaLibraryImageColumn::make('product-image')
                        ->defaultImageUrl(url('https://placehold.co/100x100.webp?text=No+Image'))
                        ->square()
                        ->size(100)
                        ->collection('product-images')
                        ->conversion('webp')
                        ->grow(false)
                        ->checkFileExistence(false)
                        ->extraImgAttributes(fn (Product $record) => [
                            'alt' => 'Product image of ' . $record->name,
                            'loading' => 'lazy',
                        ]),

                    Stack::make([
                        TextColumn::make('name')
                            ->size(TextColumnSize::Large)
                            ->weight(FontWeight::Bold),

                        TextColumn::make('price')
                            ->money('IDR'),

                        TextColumn::make('description')
                            ->markdown()
                            ->wrap(),
                    ])->space(),
                ]),
            ])
            ->groups([
                Group::make('category.name')
                    ->titlePrefixedWithLabel(false)
                    ->collapsible()
                    ->getDescriptionFromRecordUsing(function (Product $record) {
                        return $record->category?->description;
                    }),
            ])
            ->defaultGroup('category.name')
            ->groupingSettingsHidden()
            ->contentGrid([
                'md' => 1,
                'xl' => 2,
            ])
            ->contentFooter(view('components.footer.simple-page-end'))
            ->paginated(false);
    }

    public static function loginAction(): Action
    {
        return Action::make('login')
            ->button()
            ->label('Login')
            ->url(Filament::getLoginUrl());
    }

    public function render(): View
    {
        return view('livewire.home');
    }
}
