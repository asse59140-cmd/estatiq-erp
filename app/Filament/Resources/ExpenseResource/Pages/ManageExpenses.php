<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageExpenses extends ManageRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Bouton pour créer une nouvelle dépense en haut à droite
            Actions\CreateAction::make()
                ->label('Nouvelle Dépense')
                ->icon('heroicon-o-plus'),
        ];
    }
}