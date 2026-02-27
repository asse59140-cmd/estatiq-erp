<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Recalculer la taille du fichier si le fichier a changé
        if (isset($data['file_path']) && $this->record->file_path !== $data['file_path']) {
            $filePath = $data['file_path'];
            if (Storage::exists($filePath)) {
                $data['file_size'] = Storage::size($filePath);
                $data['mime_type'] = Storage::mimeType($filePath);
            }
        }
        
        return $data;
    }
}