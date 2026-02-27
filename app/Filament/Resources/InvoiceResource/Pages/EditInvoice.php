<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('download')
                ->label('Télécharger PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function ($record) {
                    // Logique de téléchargement PDF
                    return response()->download(
                        storage_path('app/invoices/' . $record->invoice_number . '.pdf'),
                        'facture-' . $record->invoice_number . '.pdf'
                    );
                }),
            Actions\Action::make('record_payment')
                ->label('Enregistrer paiement')
                ->icon('heroicon-o-currency-euro')
                ->color('primary')
                ->visible(fn ($record) => !$record->is_paid)
                ->form([
                    \Filament\Forms\Components\TextInput::make('amount')
                        ->label('Montant')
                        ->numeric()
                        ->required()
                        ->maxValue(fn ($record) => $record->balance_due),
                    \Filament\Forms\Components\Select::make('payment_method')
                        ->label('Méthode de paiement')
                        ->options([
                            'cash' => 'Espèces',
                            'check' => 'Chèque',
                            'bank_transfer' => 'Virement bancaire',
                            'credit_card' => 'Carte de crédit',
                            'online' => 'Paiement en ligne',
                        ])
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('reference')
                        ->label('Référence')
                        ->placeholder('N° de chèque, référence virement...'),
                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->rows(2),
                ])
                ->action(function ($record, array $data) {
                    $record->recordPayment($data['amount'], $data['payment_method'], [
                        'reference' => $data['reference'],
                        'notes' => $data['notes'],
                    ]);
                }),
        ];
    }
}