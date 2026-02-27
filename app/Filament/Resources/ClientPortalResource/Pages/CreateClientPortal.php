<?php

namespace App\Filament\Resources\ClientPortalResource\Pages;

use App\Filament\Resources\ClientPortalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateClientPortal extends CreateRecord
{
    protected static string $resource = ClientPortalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Définir l'agence de l'utilisateur connecté
        $data['agency_id'] = auth()->user()->agencies()->first()->id;

        // Générer un token API si non fourni
        if (empty($data['api_token'])) {
            $data['api_token'] = bin2hex(random_bytes(32));
            $data['token_expires_at'] = now()->addDays(30);
        }

        // Générer un code d'accès si non fourni
        if (empty($data['access_code'])) {
            $data['access_code'] = strtoupper(bin2hex(random_bytes(4)));
        }

        // Définir les préférences par défaut
        if (!isset($data['preferences'])) {
            $data['preferences'] = [
                'notifications_email' => true,
                'notifications_whatsapp' => true,
                'notifications_sms' => false,
                'auto_payment_reminders' => true,
                'monthly_receipts' => true,
                'language' => 'fr',
                'timezone' => 'Europe/Paris',
            ];
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}