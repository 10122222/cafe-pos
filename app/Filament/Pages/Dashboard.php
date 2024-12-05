<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        DateRangePicker::make('created_at')
                            ->label('Date Range')
                            ->defaultToday(true)
                            ->disableCustomRange()
                            ->alwaysShowCalendar(false)
                            ->autoApply(),
                    ]),
            ]);
    }
}
