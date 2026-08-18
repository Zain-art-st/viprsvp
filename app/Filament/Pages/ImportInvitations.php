<?php

namespace App\Filament\Pages;

use App\Models\CustomFieldDefinition;
use App\Models\Invitation;
use App\Models\InvitationContact;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class ImportInvitations extends Page
{
    protected string $view = 'filament.pages.import-invitations';

    public ?array $data = [];

    public array $csvHeaders = [];

    public array $csvRows = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    private function targetFieldOptions(): array
    {
        $fixed = [
            'vip_name' => 'VIP Name',
            'organization' => 'Organization',
            'vip_email' => 'VIP Email',
            'vip_phone' => 'VIP Phone',
            'pa_name' => 'Contact (PA) Name',
            'pa_email' => 'Contact (PA) Email',
        ];

        $custom = CustomFieldDefinition::where('enabled', true)
            ->pluck('label', 'field_key')
            ->mapWithKeys(fn ($label, $key) => ["custom:{$key}" => "Custom: {$label}"])
            ->toArray();

        return ['' => '— Skip this column —'] + $fixed + $custom;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Wizard\Step::make('Upload')
                        ->schema([
                            FileUpload::make('csv_file')
                                ->label('CSV File')
                                ->acceptedFileTypes(['text/csv', 'text/plain'])
                                ->required()
                                ->disk('local')
                                ->directory('csv-imports')
                                ->live()
                                ->afterStateUpdated(function ($state) {
                                    $this->loadCsvHeaders($state);
                                }),
                        ]),
                    Wizard\Step::make('Map Columns')
                        ->schema(function () {
                            if (empty($this->csvHeaders)) {
                                return [
                                    \Filament\Forms\Components\Placeholder::make('none')
                                        ->label('')
                                        ->content('Upload a CSV on the previous step first.'),
                                ];
                            }

                            return collect($this->csvHeaders)->map(
                                fn ($header) => Select::make("mapping.{$header}")
                                    ->label("Column: \"{$header}\"")
                                    ->options($this->targetFieldOptions())
                                    ->default('')
                            )->toArray();
                        }),
                ])->submitAction(
                    Action::make('import')
                        ->label('Import')
                        ->action('import')
                ),
            ])
            ->statePath('data');
    }

    private function loadCsvHeaders(?string $path): void
    {
        if (! $path) {
            $this->csvHeaders = [];
            return;
        }

        $fullPath = Storage::disk('local')->path($path);

        if (! file_exists($fullPath)) {
            $this->csvHeaders = [];
            return;
        }

        $rows = array_map('str_getcsv', file($fullPath));
        $this->csvHeaders = array_map('trim', array_shift($rows));
        $this->csvRows = $rows;
    }

    public function import(): void
    {
        $state = $this->form->getState();
        $mapping = $state['mapping'] ?? [];

        if (empty($this->csvHeaders) || empty($this->csvRows)) {
            Notification::make()->title('No CSV data to import')->danger()->send();
            return;
        }

        $created = 0;
        $skipped = 0;

        foreach ($this->csvRows as $row) {
            if (count($row) !== count($this->csvHeaders)) {
                $skipped++;
                continue;
            }

            $record = array_combine($this->csvHeaders, $row);

            $get = fn (string $target) => collect($mapping)
                ->filter(fn ($t) => $t === $target)
                ->keys()
                ->map(fn ($header) => trim($record[$header] ?? ''))
                ->first(fn ($v) => $v !== '');

            $vipName = $get('vip_name');
            $paEmail = $get('pa_email');
            $paName = $get('pa_name');

            if (! $vipName) {
                $skipped++;
                continue;
            }

            $invitation = Invitation::firstOrCreate(
                [
                    'vip_name' => $vipName,
                    'organization' => $get('organization') ?: null,
                ],
                [
                    'vip_email' => $get('vip_email') ?: null,
                    'vip_phone' => $get('vip_phone') ?: null,
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

            foreach ($mapping as $header => $target) {
                if (str_starts_with((string) $target, 'custom:')) {
                    $fieldKey = substr($target, 7);
                    $value = trim($record[$header] ?? '');

                    if ($value !== '') {
                        \App\Models\CustomFieldValue::updateOrCreate(
                            ['invitation_id' => $invitation->id, 'field_key' => $fieldKey],
                            ['value' => $value],
                        );
                    }
                }
            }

            $created++;
     }

        Notification::make()
            ->title("Import complete: {$created} rows processed, {$skipped} skipped")
            ->success()
            ->send();

        $this->csvHeaders = [];
        $this->csvRows = [];
        $this->form->fill();
   }
}