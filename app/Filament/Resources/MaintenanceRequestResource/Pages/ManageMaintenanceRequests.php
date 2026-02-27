<?php

namespace App\Filament\Resources\MaintenanceRequestResource\Pages;

use App\Filament\Resources\MaintenanceRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMaintenanceRequests extends ManageRecords
{
    protected static string $resource = MaintenanceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouveau Ticket')
                ->icon('heroicon-o-plus'),
        ];
    }
}