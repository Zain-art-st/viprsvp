<?php

namespace App\Filament\Resources\Invitations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InvitationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vip_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('organization')
                    ->searchable(),
                    TextColumn::make('vip_email')
                     ->label('VIP Email')
                    ->searchable(),
                TextColumn::make('attendance_status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'attending',
                        'danger' => 'not_attending',
                    ]),
                TextColumn::make('submitted_by_email')
                    ->label('Submitted By')
                    ->searchable(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('attendance_status')
                    ->options([
                        'pending' => 'Pending',
                        'attending' => 'Attending',
                        'not_attending' => 'Not Attending',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}