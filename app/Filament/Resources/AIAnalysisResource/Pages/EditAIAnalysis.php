<?php

namespace App\Filament\Resources\AIAnalysisResource\Pages;

use App\Filament\Resources\AIAnalysisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAIAnalysis extends EditRecord
{
    protected static string $resource = AIAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}