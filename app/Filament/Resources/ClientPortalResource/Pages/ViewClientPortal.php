<?php

namespace App\Filament\Resources\ClientPortalResource\Pages;

use App\Filament\Resources\ClientPortalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewClientPortal extends ViewRecord
{
    protected static string $resource = ClientPortalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('generate_new_code')
                ->label('Nouveau code')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->action(function ($record) {
                    $newCode = strtoupper(bin2hex(random_bytes(4)));
                    $record->update(['access_code' => $newCode]);
                    $this->notify('success', "Nouveau code généré : {$newCode}");
                }),
            
            Actions\Action::make('generate_api_token')
                ->label('Générer token')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('info')
                ->action(function ($record) {
                    $newToken = bin2hex(random_bytes(32));
                    $record->update([
                        'api_token' => $newToken,
                        'token_expires_at' => now()->addDays(30)
                    ]);
                    $this->notify('success', "Token généré - expire le {$record->token_expires_at->format('d/m/Y')}");
                }),
            
            Actions\Action::make('revoke_access')
                ->label('Révoquer')
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->visible(fn ($record) => $record->is_active)
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update(['is_active' => false]);
                    $this->notify('success', 'Accès révoqué');
                }),
            
            Actions\Action::make('restore_access')
                ->label('Restaurer')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => !$record->is_active)
                ->action(function ($record) {
                    $record->update(['is_active' => true]);
                    $this->notify('success', 'Accès restauré');
                }),
        ];
    }
}