<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Contracts\Support\Htmlable;
use ShuvroRoy\FilamentSpatieLaravelHealth\Pages\HealthCheckResults as BaseHealthCheckResults;

class HealthCheckResults extends BaseHealthCheckResults
{
    use HasPageShield;

    public static function getNavigationSort(): ?int
    {
        return 5;
    }

    public function getTitle(): string | Htmlable
    {
        return __('filament-spatie-health::health.pages.health_check_results.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return null;
    }
}
