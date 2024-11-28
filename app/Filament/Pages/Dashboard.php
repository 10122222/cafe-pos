<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate')
                            ->maxDate(fn (Get $get) => $get('endDate') ?: now())
                            ->timezone('Asia/Jakarta')
                            ->hiddenLabel()
                            ->prefix(__('pages/dashboard.filters.start.prefix'))
                            ->suffix(__('pages/dashboard.filters.start.suffix')),
                        DatePicker::make('endDate')
                            ->minDate(fn (Get $get) => $get('startDate') ?: now())
                            ->maxDate(now()->endOfDay())
                            ->timezone('Asia/Jakarta')
                            ->hiddenLabel()
                            ->prefix(__('pages/dashboard.filters.end.prefix'))
                            ->suffix(__('pages/dashboard.filters.end.suffix')),
                    ])
                    ->columns(),
            ]);
    }
}
