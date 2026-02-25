<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Locataires';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identité')
                    ->schema([
                        Forms\Components\TextInput::make('full_name')->label('Nom complet')->required(),
                        Forms\Components\TextInput::make('email')->label('Email')->email(),
                        Forms\Components\TextInput::make('phone')->label('Téléphone'),
                    ])->columns(3),
                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\Select::make('property_id')->label('Propriété')->relationship('property', 'title')->required(),
                        Forms\Components\DatePicker::make('lease_start')->label('Début du bail')->required(),
                        Forms\Components\DatePicker::make('lease_end')->label('Fin du bail'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->label('Locataire')->searchable(),
                Tables\Columns\TextColumn::make('property.title')->label('Propriété')->badge()->color('info'),
                Tables\Columns\TextColumn::make('lease_start')->label('Début')->date('d/m/Y'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('downloadLease')
                    ->label('Générer Bail')
                    ->icon('heroicon-o-document-text')
                    ->color('warning')
                    ->action(function (Tenant $record) {
                        $pdf = Pdf::loadHtml('<h1>Bail de Location - ' . $record->full_name . '</h1><p>Propriété : ' . ($record->property->title ?? 'N/A') . '</p><p>Début du bail : ' . $record->lease_start . '</p><p><i>Document auto-généré par ESTATIQ.</i></p>');
                        return response()->streamDownload(fn () => print($pdf->stream()), "Bail_{$record->id}.pdf");
                    }),
            ]);
    }

    public static function getPages(): array { return ['index' => Pages\ListTenants::route('/'), 'create' => Pages\CreateTenant::route('/create'), 'edit' => Pages\EditTenant::route('/{record}/edit')]; }
}