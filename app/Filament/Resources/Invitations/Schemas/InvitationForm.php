<?php

namespace App\Filament\Resources\Invitations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InvitationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('vip_name')
                    ->required(),
                TextInput::make('organization'),
                Select::make('attendance_status')
                    ->options([
                        'pending' => 'Pending',
                        'attending' => 'Attending',
                        'not_attending' => 'Not Attending',
                    ])
                    ->required()
                    ->default('pending'),
                TextInput::make('vehicle_registration'),
                TextInput::make('estimated_arrival'),
                TextInput::make('estimated_departure'),
                TextInput::make('submitted_by_name')
                    ->disabled(),
                TextInput::make('submitted_by_email')
                    ->email()
                    ->disabled(),
                DateTimePicker::make('submitted_at')
                    ->disabled(),
                DateTimePicker::make('expires_at'),
            ]);
    }
}