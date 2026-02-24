<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-users'; // Icône plus adaptée
    
    protected static ?string $navigationLabel = 'Locataires';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identité du Locataire')
                    ->schema([
                        Forms\Components\TextInput::make('full_name')
                            ->label('Nom complet')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone'),
                    ])->columns(2),

                Forms\Components\Section::make('Location & Bail')
                    ->schema([
                        Forms\Components\Select::make('property_id')
                            ->label('Propriété assignée')
                            ->relationship('property', 'title') // Lie le locataire à une villa/appart
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DatePicker::make('lease_start')
                            ->label('Date de début de bail')
                            ->required(),
                        Forms\Components\DatePicker::make('lease_end')
                            ->label('Date de fin de bail'),
                    ])->columns(2),

                Forms\Components\Section::make('Documents Administratifs')
                    ->schema([
                        Forms\Components\FileUpload::make('lease_document')
                            ->label('Contrat de bail (PDF)')
                            ->directory('leases')
                            ->acceptedFileTypes(['application/pdf'])
                            ->preserveFilenames()
                            ->openable()
                            ->downloadable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Locataire')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('property.title')
                    ->label('Propriété')
                    ->badge()
                    ->color('info')
                    ->placeholder('Non assigné'),
                Tables\Columns\TextColumn::make('lease_start')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lease_end')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}