<?php

namespace App\Filament\Resources\GuarantorResource\Pages;

use App\Filament\Resources\GuarantorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuarantor extends EditRecord
{
    protected static string $resource = GuarantorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('verify')
                ->label('Vérifier')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => !$record->is_verified)
                ->action(fn ($record) => $record->update([
                    'documents_verified' => true,
                    'verified_at' => now(),
                    'verified_by' => auth()->id(),
                ])),
        ];
    }
}