<?php

namespace App\Filament\Resources\SignatureRequestResource\Pages;

use App\Filament\Resources\SignatureRequestResource;
use App\Services\ElectronicSignatureService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ViewSignatureRequest extends ViewRecord
{
    protected static string $resource = SignatureRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('send')
                ->label('Envoyer')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn ($record) => $record->status === 'pending')
                ->action(function ($record) {
                    try {
                        $service = new ElectronicSignatureService($record->provider);
                        $result = $service->createLeaseContractEnvelope(
                            $record->requestable,
                            $record->signers,
                            $record->document
                        );
                        
                        $record->update([
                            'envelope_id' => $result['envelope_id'],
                            'status' => 'sent',
                            'sent_date' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Demande envoyée')
                            ->body('La demande de signature a été envoyée avec succès.')
                            ->success()
                            ->send();
                            
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erreur d\'envoi')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            
            Actions\Action::make('refresh_status')
                ->label('Actualiser')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn ($record) => $record->envelope_id && in_array($record->status, ['sent', 'viewed']))
                ->action(function ($record) {
                    try {
                        $service = new ElectronicSignatureService($record->provider);
                        $status = $service->getEnvelopeStatus($record->envelope_id);
                        
                        // Mise à jour du statut et des signataires
                        $updatedSigners = $record->signers;
                        
                        if (isset($status['recipients']) && is_array($status['recipients'])) {
                            foreach ($status['recipients'] as $recipient) {
                                foreach ($updatedSigners as &$signer) {
                                    if ($signer['email'] === $recipient['email']) {
                                        $signer['status'] = $recipient['status'] ?? 'pending';
                                        $signer['signed_at'] = $recipient['completedDateTime'] ?? null;
                                    }
                                }
                            }
                        }
                        
                        $newStatus = match($status['status'] ?? 'unknown') {
                            'sent' => 'sent',
                            'delivered' => 'viewed',
                            'completed' => 'completed',
                            'declined' => 'declined',
                            'voided' => 'cancelled',
                            'expired' => 'expired',
                            default => $record->status
                        };
                        
                        $updateData = [
                            'status' => $newStatus,
                            'signers' => $updatedSigners,
                        ];
                        
                        if ($newStatus === 'completed') {
                            $updateData['completed_date'] = now();
                            
                            // Télécharger le document signé
                            try {
                                $signedPath = $service->downloadSignedDocument($record->envelope_id);
                                if ($signedPath) {
                                    $updateData['signed_document_path'] = $signedPath;
                                }
                            } catch (\Exception $e) {
                                // Ignorer l'erreur de téléchargement
                            }
                        }
                        
                        $record->update($updateData);
                        
                        Notification::make()
                            ->title('Statut actualisé')
                            ->body("Le statut a été mis à jour : {$newStatus}")
                            ->success()
                            ->send();
                            
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erreur d\'actualisation')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            
            Actions\Action::make('download_signed')
                ->label('Télécharger signé')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->visible(fn ($record) => $record->status === 'completed' && $record->signed_document_path)
                ->action(function ($record) {
                    if ($record->signed_document_path && Storage::exists($record->signed_document_path)) {
                        return response()->download(Storage::path($record->signed_document_path));
                    } else {
                        Notification::make()
                            ->title('Document non disponible')
                            ->body('Le document signé n\'est pas disponible.')
                            ->warning()
                            ->send();
                    }
                }),
            
            Actions\Action::make('cancel')
                ->label('Annuler')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => $record->canBeCancelled())
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update(['status' => 'cancelled']);
                    
                    Notification::make()
                        ->title('Demande annulée')
                        ->success()
                        ->send();
                }),
        ];
    }
}