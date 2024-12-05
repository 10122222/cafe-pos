<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Illuminate\Contracts\Support\Htmlable;

class Reports extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public function getTitle(): string | Htmlable
    {
        return __('clusters/pages/report.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('clusters/pages/report.title');
    }

    public static function getNavigationGroup(): string
    {
        return __('navigation.group.settings');
    }

    public static function getNavigationSort(): ?int
    {
        return 6;
    }
}
