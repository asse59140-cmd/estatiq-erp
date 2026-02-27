<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Générer un numéro d'employé automatiquement si non fourni
        if (empty($data['employee_number'])) {
            $latestEmployee = \App\Models\Employee::orderBy('id', 'desc')->first();
            $nextNumber = $latestEmployee ? intval(substr($latestEmployee->employee_number, 3)) + 1 : 1;
            $data['employee_number'] = 'EMP' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }

        // Définir l'agence de l'utilisateur connecté
        $data['agency_id'] = auth()->user()->agencies()->first()->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}