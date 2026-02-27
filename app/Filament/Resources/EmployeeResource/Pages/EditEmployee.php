<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('attendance')
                ->label('Présence')
                ->icon('heroicon-o-clock')
                ->color('info')
                ->url(fn ($record) => route('filament.admin.resources.employees.attendance', $record)),
        ];
    }
}