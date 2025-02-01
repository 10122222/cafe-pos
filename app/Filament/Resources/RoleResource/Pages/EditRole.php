<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use BezhanSalleh\FilamentShield\Resources\RoleResource\Pages\EditRole as BaseEditRole;
use Filament\Actions;
use Filament\Notifications;

class EditRole extends BaseEditRole
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reset')
                ->hiddenLabel()
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->tooltip('Reset')
                ->action(fn () => $this->fillForm()),
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->successNotification(
                    Notifications\Notification::make()
                        ->success()
                        ->title('Role deleted')
                        ->body('The role has been deleted successfully.')
                ),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notifications\Notification
    {
        return Notifications\Notification::make()
            ->success()
            ->title('Role updated')
            ->body('The role has been updated successfully.');
    }
}
