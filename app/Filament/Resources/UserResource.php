<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 3;

    /** @return string[] */
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
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255)
                    ->autofocus(fn (string $operation) => $operation === 'create')
                    ->inlineLabel(),

                Forms\Components\TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->inlineLabel(),

                Forms\Components\TextInput::make('password')
                    ->label(fn (?User $record) => $record === null ? 'Password' : 'New password')
                    ->password()
                    ->revealable()
                    ->rule(Password::default())
                    ->autocomplete('new-password')
                    ->dehydrated(fn ($state): bool => filled($state))
                    ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                    ->live(debounce: 500)
                    ->same('passwordConfirmation')
                    ->inlineLabel(),

                Forms\Components\TextInput::make('passwordConfirmation')
                    ->label(fn (?User $record) => $record === null ? 'Confirm password' : 'Confirm new password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->visible(fn (Forms\Get $get): bool => filled($get('password')))
                    ->dehydrated(false)
                    ->inlineLabel(),

                Forms\Components\Toggle::make('email_verified_at')
                    ->label('Verified')
                    ->onColor('success')
                    ->offColor('danger')
                    ->default(null)
                    ->dehydrateStateUsing(function ($state, $record) {
                        if ($record && $state === ($record->email_verified_at !== null)) {
                            return $record->email_verified_at;
                        }

                        return $state ? now() : null;
                    })
                    ->afterStateHydrated(fn ($component, $state) => $component->state($state !== null))
                    ->inlineLabel(),

                Forms\Components\Select::make('roles')
                    ->multiple()
                    ->maxItems(1)
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Role $record): string => Str::headline($record->name))
                    ->preload()
                    ->label('Role')
                    ->inlineLabel(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    /** @return Builder<User> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('roles');
    }
}
