<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuildingResource\Pages;
use App\Models\Building;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;

class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    
    protected static ?string $navigationLabel = 'Immeubles';
    
    protected static ?string $navigationGroup = 'Gestion Immobilière';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations Générales')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom de l\'immeuble')
                            ->required()
                            ->placeholder('Ex: Résidence Les Palmiers'),
                        
                        Forms\Components\Select::make('building_type')
                            ->label('Type d\'immeuble')
                            ->options([
                                'residential' => 'Résidentiel',
                                'commercial' => 'Commercial',
                                'mixed' => 'Mixte',
                                'office' => 'Bureaux',
                                'retail' => 'Commerce',
                            ])
                            ->default('residential')
                            ->required(),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->rows(3),
                    ])->columns(2),

                Section::make('Adresse')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->label('Adresse complète')
                            ->required()
                            ->placeholder('123 Rue de la Paix'),
                        
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('city')
                                    ->label('Ville')
                                    ->required(),
                                
                                Forms\Components\TextInput::make('postal_code')
                                    ->label('Code postal')
                                    ->required()
                                    ->maxLength(10),
                                
                                Forms\Components\TextInput::make('country')
                                    ->label('Pays')
                                    ->default('France')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Caractéristiques Techniques')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('construction_year')
                                    ->label('Année de construction')
                                    ->numeric()
                                    ->minValue(1800)
                                    ->maxValue(date('Y') + 10),
                                
                                Forms\Components\TextInput::make('total_floors')
                                    ->label('Nombre d\'étages')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1),
                                
                                Forms\Components\TextInput::make('elevator_count')
                                    ->label('Nombre d\'ascenseurs')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                
                                Forms\Components\TextInput::make('parking_spaces')
                                    ->label('Places de parking')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                            ]),
                        
                        Forms\Components\Select::make('energy_rating')
                            ->label('Classe énergétique')
                            ->options([
                                'A' => 'A - Excellente',
                                'B' => 'B - Très bonne',
                                'C' => 'C - Bonne',
                                'D' => 'D - Moyenne',
                                'E' => 'E - Médiocre',
                                'F' => 'F - Mauvaise',
                                'G' => 'G - Très mauvaise',
                            ])
                            ->nullable(),
                    ]),

                Section::make('Localisation GPS')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('lat')
                                    ->label('Latitude')
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->placeholder('48.856614'),
                                
                                Forms\Components\TextInput::make('lng')
                                    ->label('Longitude')
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->placeholder('2.3522219'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Équipements')
                    ->schema([
                        Forms\Components\CheckboxList::make('amenities')
                            ->label('Équipements disponibles')
                            ->options([
                                'elevator' => 'Ascenseur',
                                'parking' => 'Parking',
                                'garden' => 'Jardin',
                                'terrace' => 'Terrasse',
                                'pool' => 'Piscine',
                                'gym' => 'Salle de sport',
                                'concierge' => 'Concierge',
                                'security' => 'Système de sécurité',
                                'intercom' => 'Interphone',
                                'videophone' => 'Vidéophone',
                                'waste_sorting' => 'Tri des déchets',
                                'bike_storage' => 'Local à vélos',
                            ])
                            ->columns(3),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Médias')
                    ->schema([
                        Forms\Components\FileUpload::make('images')
                            ->label('Photos de l\'immeuble')
                            ->multiple()
                            ->directory('buildings/' . date('Y/m'))
                            ->image()
                            ->maxSize(5120) // 5MB
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Relations')
                    ->schema([
                        Forms\Components\Select::make('owner_id')
                            ->label('Propriétaire')
                            ->relationship('owner', 'full_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('address')
                    ->label('Adresse')
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('city')
                    ->label('Ville')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('building_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'residential' => 'success',
                        'commercial' => 'primary',
                        'mixed' => 'warning',
                        'office' => 'info',
                        'retail' => 'danger',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('total_floors')
                    ->label('Étages')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('units_count')
                    ->label('Unités')
                    ->getStateUsing(fn ($record) => $record->units()->count()),
                
                Tables\Columns\TextColumn::make('energy_rating')
                    ->label('Classe énergie')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'A', 'B' => 'success',
                        'C', 'D' => 'warning',
                        'E', 'F', 'G' => 'danger',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('owner.full_name')
                    ->label('Propriétaire')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('building_type')
                    ->label('Type d\'immeuble')
                    ->options([
                        'residential' => 'Résidentiel',
                        'commercial' => 'Commercial',
                        'mixed' => 'Mixte',
                        'office' => 'Bureaux',
                        'retail' => 'Commerce',
                    ]),
                
                Tables\Filters\SelectFilter::make('energy_rating')
                    ->label('Classe énergétique')
                    ->options([
                        'A' => 'A',
                        'B' => 'B',
                        'C' => 'C',
                        'D' => 'D',
                        'E' => 'E',
                        'F' => 'F',
                        'G' => 'G',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuildings::route('/'),
            'create' => Pages\CreateBuilding::route('/create'),
            'view' => Pages\ViewBuilding::route('/{record}'),
            'edit' => Pages\EditBuilding::route('/{record}/edit'),
        ];
    }
}