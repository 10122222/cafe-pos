<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Pages\SimplePage;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Table\Concerns\HasHeaderActions;
use Illuminate\Contracts\Support\Htmlable;

class Welcome extends SimplePage
{
    use HasHeaderActions;

    protected static string $view = 'filament.pages.welcome';

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }
    }

    public function getMaxWidth(): MaxWidth | string | null
    {
        return MaxWidth::ScreenLarge;
    }

    public function getTitle(): string | Htmlable
    {
        return 'Welcome';
    }

    public function getHeading(): string | Htmlable
    {
        return 'Welcome to CafePOS';
    }

    public function hasLogo(): bool
    {
        return false;
    }
}
