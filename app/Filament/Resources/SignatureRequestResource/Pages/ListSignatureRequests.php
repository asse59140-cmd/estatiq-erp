<?php

namespace App\Filament\Resources\SignatureRequestResource\Pages;

use App\Filament\Resources\SignatureRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSignatureRequests extends ListRecords
{
    protected static string $resource = SignatureRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvelle Demande'),
        ];
    }
}