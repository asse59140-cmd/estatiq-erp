<?php

namespace App\Filament\Resources\GuarantorResource\Pages;

use App\Filament\Resources\GuarantorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGuarantor extends CreateRecord
{
    protected static string $resource = GuarantorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Définir l'agence de l'utilisateur connecté
        $data['agency_id'] = auth()->user()->agencies()->first()->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}