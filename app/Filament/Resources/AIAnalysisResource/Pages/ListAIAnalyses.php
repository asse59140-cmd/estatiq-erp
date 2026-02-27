<?php

namespace App\Filament\Resources\AIAnalysisResource\Pages;

use App\Filament\Resources\AIAnalysisResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAIAnalyses extends ListRecords
{
    protected static string $resource = AIAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nouvelle Analyse'),
        ];
    }
}