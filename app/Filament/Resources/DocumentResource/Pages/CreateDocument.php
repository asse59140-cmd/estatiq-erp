<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Le Trait KoreErpBelongsToAgency s'occupe automatiquement de l'agence
        $data['uploaded_by'] = auth()->id();
        
        // Calculer la taille du fichier
        if (isset($data['file_path'])) {
            $filePath = $data['file_path'];
            if (Storage::exists($filePath)) {
                $data['file_size'] = Storage::size($filePath);
                $data['mime_type'] = Storage::mimeType($filePath);
            }
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}