<?php

namespace App\Filament\Pages\Auth;

use App\Models\Agency;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant as BaseRegisterTenant; // On lui donne un surnom
use Illuminate\Support\Str;

class RegisterTenant extends BaseRegisterTenant // On hérite du "surnom"
{
    public static function getLabel(): string
    {
        return 'Enregistrer votre agence';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nom de l\'agence')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->label('Identifiant unique (URL)')
                    ->required()
                    ->unique(Agency::class, 'slug'),
            ]);
    }

    protected function handleRegistration(array $data): Agency
    {
        $agency = Agency::create($data);

        // On lie l'utilisateur actuel à cette nouvelle agence
        $agency->members()->attach(auth()->user());

        return $agency;
    }
}