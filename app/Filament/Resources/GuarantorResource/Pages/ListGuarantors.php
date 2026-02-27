<?php

namespace App\Filament\Resources\GuarantorResource\Pages;

use App\Filament\Resources\GuarantorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuarantors extends ListRecords
{
    protected static string $resource = GuarantorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouveau Garant'),
        ];
    }
}