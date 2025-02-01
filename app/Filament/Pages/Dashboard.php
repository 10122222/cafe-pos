<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    protected static string $routePath = '/dashboard';

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        DateRangePicker::make('created_at')
                            ->label('Date range')
                            ->defaultThisMonth()
                            ->alwaysShowCalendar(false)
                            ->autoApply(),
                    ]),
            ]);
    }
}
