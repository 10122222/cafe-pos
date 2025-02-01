<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Facades\FilamentView;
use function Filament\Support\is_app_url;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

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
                                    ->columnSpanFull(),
                            ])
                            ->columns(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Infolists\Components\Group::make()
                    ->schema([
                        Infolists\Components\Section::make('Status')
                            ->schema([
                                Infolists\Components\IconEntry::make('is_visible')
                                    ->label('Visible to customers'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
