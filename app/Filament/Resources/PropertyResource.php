<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    
    protected static ?string $navigationLabel = 'Propriétés';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations Générales')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Nom de la propriété')
                            ->required()
                            ->placeholder('ex: Villa Zeytoune'),
                        Forms\Components\TextInput::make('address')
                            ->label('Adresse complète')
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->label('Description détaillée')
                            ->columnSpanFull(),
                    ])->columnSpan(2),

                Forms\Components\Section::make('Détails & Statut')
                    ->schema([
                        Forms\Components\Select::make('owner_id')
                            ->label('Propriétaire')
                            ->relationship('owner', 'full_name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('price')
                            ->label('Loyer')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                        Forms\Components\TextInput::make('type')
                            ->label('Type de bien (ex: Villa, Appartement)')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'available' => 'Disponible',
                                'rented' => 'Loué',
                                'maintenance' => 'En travaux',
                            ])
                            ->default('available')
                            ->required(),
                        Forms\Components\FileUpload::make('images')
                            ->label('Photos')
                            ->multiple()
                            ->directory('properties')
                            ->columnSpanFull(),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('owner.full_name')
                    ->label('Propriétaire')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Loyer')
                    ->money('eur')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->colors([
                        'success' => 'available',
                        'warning' => 'rented',
                        'danger' => 'maintenance',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Disponible',
                        'rented' => 'Loué',
                        'maintenance' => 'En travaux',
                    ]),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}