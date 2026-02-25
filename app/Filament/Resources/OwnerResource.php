<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OwnerResource\Pages;
use App\Models\Owner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;

class OwnerResource extends Resource
{
    protected static ?string $model = Owner::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Propriétaires';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identité')
                    ->schema([
                        Forms\Components\TextInput::make('full_name')->label('Nom complet')->required(),
                        Forms\Components\TextInput::make('email')->email()->required(),
                        Forms\Components\TextInput::make('phone')->tel(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->label('Nom')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('downloadReport')
                    ->label('Bilan Financier')
                    ->icon('heroicon-o-chart-bar')
                    ->color('success')
                    ->action(function (Owner $record) {
                        $pdf = Pdf::loadHtml('<h1>Compte Rendu de Gérance - ' . $record->full_name . '</h1><p>Ce document liste les revenus et les charges du propriétaire.</p><p><i>Module comptable ESTATIQ en cours de liaison...</i></p>');
                        return response()->streamDownload(fn () => print($pdf->stream()), "CRG_{$record->id}.pdf");
                    }),
            ]);
    }

    public static function getPages(): array { return ['index' => Pages\ListOwners::route('/'), 'create' => Pages\CreateOwner::route('/create'), 'edit' => Pages\EditOwner::route('/{record}/edit')]; }
}