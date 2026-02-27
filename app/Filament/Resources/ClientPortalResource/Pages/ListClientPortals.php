<?php

namespace App\Filament\Resources\ClientPortalResource\Pages;

use App\Filament\Resources\ClientPortalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClientPortals extends ListRecords
{
    protected static string $resource = ClientPortalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouveau Portail'),
        ];
    }
}