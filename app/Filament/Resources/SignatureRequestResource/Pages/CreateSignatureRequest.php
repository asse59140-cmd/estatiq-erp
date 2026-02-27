<?php

namespace App\Filament\Resources\SignatureRequestResource\Pages;

use App\Filament\Resources\SignatureRequestResource;
use App\Services\ElectronicSignatureService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateSignatureRequest extends CreateRecord
{
    protected static string $resource = SignatureRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Définir l'agence et l'utilisateur connecté
        $data['agency_id'] = auth()->user()->agencies()->first()->id;
        $data['created_by'] = auth()->id();
        $data['request_date'] = now();
        
        // Valider et formater les signataires
        if (isset($data['signers']) && is_array($data['signers'])) {
            $data['signers'] = array_map(function ($signer, $index) {
                return [
                    'name' => $signer['name'] ?? '',
                    'email' => $signer['email'] ?? '',
                    'phone' => $signer['phone'] ?? '',
                    'role' => $signer['role'] ?? 'other',
                    'order' => $signer['order'] ?? ($index + 1),
                    'page' => $signer['page'] ?? 1,
                    'x' => $signer['x'] ?? 100,
                    'y' => $signer['y'] ?? 100,
                    'status' => 'pending',
                    'signed_at' => null,
                ];
            }, $data['signers'], array_keys($data['signers']));
        }
        
        // Trier les signataires par ordre
        if (isset($data['signers'])) {
            usort($data['signers'], function ($a, $b) {
                return $a['order'] <=> $b['order'];
            });
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Envoyer automatiquement la demande si configuré
        if ($this->record->auto_send ?? true) {
            try {
                $service = new ElectronicSignatureService($this->record->provider);
                $result = $service->createLeaseContractEnvelope(
                    $this->record->requestable,
                    $this->record->signers,
                    $this->record->document
                );
                
                $this->record->update([
                    'envelope_id' => $result['envelope_id'],
                    'status' => 'sent',
                    'sent_date' => now(),
                ]);
                
                Notification::make()
                    ->title('Demande créée et envoyée')
                    ->body('La demande de signature a été créée et envoyée avec succès.')
                    ->success()
                    ->send();
                    
            } catch (\Exception $e) {
                Notification::make()
                    ->title('Erreur d\'envoi')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}