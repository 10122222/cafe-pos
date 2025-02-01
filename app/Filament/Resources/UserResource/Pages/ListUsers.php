<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(isIndividual: true, isGlobal: false)
                    ->sortable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Verified')
                    ->badge()
                    ->getStateUsing(fn (User $record) => $record->email_verified_at ? 'Verified' : 'Unverified')
                    ->color(fn (string $state) => $state === 'Verified' ? 'success' : 'danger')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('roles')
                    ->label('Role')
                    ->searchable(isGlobal: false)
                    ->toggleable()
                    ->getStateUsing(fn ($record) => $record->roles->pluck('name')->join(', '))
                    ->formatStateUsing(fn ($state): string => Str::headline($state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Join date')
                    ->date(timezone: 'Asia/Jakarta')
                    ->dateTimeTooltip(timezone: 'Asia/Jakarta')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('verified')
                    ->label('Verified')
                    ->options([
                        'verified' => 'Verified',
                        'unverified' => 'Unverified',
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
                    ->label('Role')
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
}
