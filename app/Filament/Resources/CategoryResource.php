<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CategoryResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Category::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 0;

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
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('resources/category.name'))
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
                                    ->label(__('resources/category.slug'))
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Category::class, 'slug', ignoreRecord: true),
                            ]),

                        Forms\Components\Toggle::make('is_visible')
                            ->label(__('resources/category.is_visible'))
                            ->default(true),

                        Forms\Components\MarkdownEditor::make('description')
                            ->label(__('resources/category.description')),
                    ])
                    ->columnSpan(['lg' => fn (?Category $record) => $record === null ? 3 : 2]),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label(__('resources/category.created_at'))
                            ->content(fn (Category $record): ?string => $record->created_at?->setTimezone('Asia/Jakarta')->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label(__('resources/category.updated_at'))
                            ->content(fn (Category $record): ?string => $record->updated_at?->setTimezone('Asia/Jakarta')->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Category $record) => $record === null),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('resources/category.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('resources/category.visibility'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_products')
                    ->label(__('resources/category.total_products'))
                    ->sortable(query: function (Builder $query, string $direction) {
                        $query->withCount('products')->orderBy('products_count', $direction);
                    })
                    ->getStateUsing(fn (Category $record) => $record->products()->count()),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('resources/category.updated_at'))
                    ->date(timezone: 'Asia/Jakarta')
                    ->dateTimeTooltip(timezone: 'Asia/Jakarta')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Grid::make()
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label(__('resources/category.name')),
                            ]),

                        Infolists\Components\IconEntry::make('is_visible')
                            ->label(__('resources/category.visibility'))
                            ->icon(fn (string $state): string => match ($state) {
                                '1' => 'heroicon-o-check-circle',
                                default => 'heroicon-o-x-circle',
                            }),

                        Infolists\Components\TextEntry::make('description')
                            ->markdown()
                            ->label(__('resources/category.description'))
                            ->placeholder(__('resources/category.no_description')),
                    ])
                    ->columnSpan(['lg' => 2]),
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label(__('resources/category.created_at'))
                            ->since()
                            ->dateTimeTooltip(timezone: 'Asia/Jakarta'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label(__('resources/category.updated_at'))
                            ->since()
                            ->dateTimeTooltip(timezone: 'Asia/Jakarta'),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view' => Pages\ViewCategory::route('/{record}'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('resources/category.single');
    }

    public static function getModelLabelPlural(): string
    {
        return __('resources/category.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('resources/category.nav.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('resources/category.plural');
    }
}
