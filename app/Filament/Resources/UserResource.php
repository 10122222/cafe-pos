<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
                Forms\Components\TextInput::make('name')
                    ->label(__('resources/user.name'))
                    ->required()
                    ->maxLength(255)
                    ->autofocus(fn (string $operation) => $operation === 'create')
                    ->inlineLabel(),

                Forms\Components\TextInput::make('email')
                    ->label(__('resources/user.email'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->inlineLabel(),

                Forms\Components\TextInput::make('password')
                    ->label(fn (?User $record) => $record === null ? __('resources/user.password') : __('resources/user.new_password'))
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
                    ->label(fn (?User $record) => $record === null ? __('resources/user.password_confirmation') : __('resources/user.new_password_confirmation'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->visible(fn (Get $get): bool => filled($get('password')))
                    ->dehydrated(false)
                    ->inlineLabel(),

                Forms\Components\Toggle::make('email_verified_at')
                    ->label(__('resources/user.verified'))
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
                    ->searchable()
                    ->label(__('resources/user.roles'))
                    ->inlineLabel(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('resources/user.name'))
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('resources/user.email'))
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label(__('resources/user.verified'))
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->email_verified_at ? 'Verified' : 'Not Verified')
                    ->color(fn ($state) => $state === 'Verified' ? 'success' : 'danger')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('roles')
                    ->label(__('resources/user.roles'))
                    ->searchable(isGlobal: false)
                    ->toggleable()
                    ->getStateUsing(fn ($record) => $record->roles->pluck('name')->join(', '))
                    ->formatStateUsing(fn ($state): string => Str::headline($state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('resources/user.created_at'))
                    ->date(timezone: 'Asia/Jakarta')
                    ->dateTimeTooltip(timezone: 'Asia/Jakarta')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('verified')
                    ->label(__('resources/user.verified'))
                    ->options([
                        'verified' => 'Verified',
                        'unverified' => 'Not Verified',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value'] === 'verified',
                                fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')
                            )
                            ->when(
                                $data['value'] === 'unverified',
                                fn (Builder $query): Builder => $query->whereNull('email_verified_at')
                            );
                    }),

                Tables\Filters\SelectFilter::make('role')
                    ->relationship('roles', 'name')
                    ->label(__('resources/user.roles'))
                    ->getOptionLabelFromRecordUsing(fn (Role $record): string => Str::headline($record->name)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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

    public static function getModelLabel(): string
    {
        return __('resources/user.single');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources/user.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('resources/user.nav.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('resources/user.plural');
    }
}
