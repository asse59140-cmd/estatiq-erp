<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Définir l'agence de l'utilisateur connecté
        $data['agency_id'] = auth()->user()->agencies()->first()->id;
        
        // Calculer les totaux
        $data['subtotal'] = 0;
        $data['tax_amount'] = 0;
        $data['total_amount'] = 0;
        $data['paid_amount'] = 0;
        $data['balance_due'] = 0;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}