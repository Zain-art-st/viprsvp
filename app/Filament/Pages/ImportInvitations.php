<?php

namespace App\Filament\Pages;

use App\Models\Invitation;
use App\Models\InvitationContact;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class ImportInvitations extends Page
{
    protected string $view = 'filament.pages.import-invitations';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('csv_file')
                    ->label('CSV File')
                    ->acceptedFileTypes(['text/csv', 'text/plain'])
                    ->required()
                    ->disk('local')
                    ->directory('csv-imports'),
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        $state = $this->form->getState();
        $path = $state['csv_file'];

        $fullPath = Storage::disk('local')->path($path);
        $rows = array_map('str_getcsv', file($fullPath));
        $header = array_map('trim', array_shift($rows));

        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            if (count($row) !== count($header)) {
                $skipped++;
                continue;
            }

            $record = array_combine($header, $row);

            $vipName = trim($record['vip_name'] ?? '');
            $paName = trim($record['pa_name'] ?? '');
            $paEmail = trim($record['pa_email'] ?? '');

            if (! $vipName ) {
                $skipped++;
                continue;
            }

            $invitation = Invitation::firstOrCreate(
    [
        'vip_name' => $vipName,
        'organization' => trim($record['organization'] ?? '') ?: null,
    ],
    [
        'vip_email' => trim($record['vip_email'] ?? '') ?: null,
        'vip_phone' => trim($record['vip_phone'] ?? '') ?: null,
    ],
);
if ($paEmail) {
    InvitationContact::firstOrCreate(
        [
            'invitation_id' => $invitation->id,
            'email' => $paEmail,
        ],
        [
            'name' => $paName ?: $paEmail,
            'token' => \Illuminate\Support\Str::random(40),
        ],
    );
}

InvitationContact::firstOrCreate(
    [
        'invitation_id' => $invitation->id,
        'email' => $paEmail,
    ],
    [
        'name' => $paName ?: $paEmail,
        'token' => \Illuminate\Support\Str::random(40),
    ],
);

$created++;
            
        }

        Storage::disk('local')->delete($path);

        Notification::make()
            ->title("Import complete: {$created} contacts processed, {$skipped} rows skipped")
            ->success()
            ->send();

        $this->form->fill();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('import')
                ->label('Import')
                ->action('import'),
        ];
    }
}