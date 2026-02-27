<?php

namespace App\Filament\Resources\AIAnalysisResource\Pages;

use App\Filament\Resources\AIAnalysisResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAIAnalysis extends ViewRecord
{
    protected static string $resource = AIAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            Actions\Action::make('validate')
                ->label('Valider')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn ($record) => $record->status === 'completed' && !$record->is_validated)
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->validateAnalysis(auth()->user(), true);
                    
                    Notification::make()
                        ->title('Analyse validée')
                        ->success()
                        ->send();
                }),
            
            Actions\Action::make('reject')
                ->label('Rejeter')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => $record->status === 'completed' && !$record->is_validated)
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->validateAnalysis(auth()->user(), false);
                    
                    Notification::make()
                        ->title('Analyse rejetée')
                        ->warning()
                        ->send();
                }),
            
            Actions\Action::make('regenerate')
                ->label('Régénérer')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn ($record) => in_array($record->status, ['failed', 'completed']))
                ->requiresConfirmation()
                ->action(function ($record) {
                    // Logique de régénération à implémenter
                    Notification::make()
                        ->title('Régénération lancée')
                        ->info()
                        ->send();
                }),
        ];
    }
}