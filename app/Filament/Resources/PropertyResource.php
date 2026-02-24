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
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->label('Type de bien')
                            ->options([
                                'villa' => 'Villa',
                                'appartement' => 'Appartement',
                                'bureau' => 'Bureau',
                            ])->required(),
                        Forms\Components\TextInput::make('price')
                            ->label('Loyer mensuel')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('État')
                            ->options([
                                'available' => 'Disponible',
                                'rented' => 'Loué',
                                'maintenance' => 'En travaux',
                            ])->default('available'),
                    ])->columnSpan(1),

                Forms\Components\Section::make('Galerie Photos')
                    ->schema([
                        Forms\Components\FileUpload::make('images')
                            ->label('Photos du bien')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->directory('properties')
                            ->visibility('public')
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images')
                    ->label('Photo')
                    ->circular()
                    ->stacked()
                    ->limit(3),
                Tables\Columns\TextColumn::make('title')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('owner.full_name')
                    ->label('Propriétaire'),
                Tables\Columns\TextColumn::make('price')
                    ->label('Loyer')
                    ->money('eur'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
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