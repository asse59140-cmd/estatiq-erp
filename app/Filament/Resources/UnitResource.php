<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static ?string $navigationLabel = 'Unités';
    
    protected static ?string $navigationGroup = 'Gestion Immobilière';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations Générales')
                    ->schema([
                        Forms\Components\TextInput::make('unit_number')
                            ->label('Numéro d\'unité')
                            ->placeholder('Ex: A101, B-205')
                            ->required(),
                        
                        Forms\Components\Select::make('building_id')
                            ->label('Immeuble')
                            ->relationship('building', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\Select::make('unit_type')
                            ->label('Type d\'unité')
                            ->options([
                                'studio' => 'Studio',
                                'apartment' => 'Appartement',
                                'duplex' => 'Duplex',
                                'penthouse' => 'Penthouse',
                                'office' => 'Bureau',
                                'retail' => 'Commerce',
                                'warehouse' => 'Entrepôt',
                            ])
                            ->default('apartment')
                            ->required(),
                        
                        Forms\Components\TextInput::make('floor')
                            ->label('Étage')
                            ->numeric()
                            ->default(1)
                            ->minValue(-2)
                            ->maxValue(100),
                    ])
                    ->columns(2),

                Section::make('Caractéristiques')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('area_sqm')
                                    ->label('Surface (m²)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->suffix('m²'),
                                
                                Forms\Components\TextInput::make('bedrooms')
                                    ->label('Chambres')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(0),
                                
                                Forms\Components\TextInput::make('bathrooms')
                                    ->label('Salles de bain')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1),
                                
                                Forms\Components\TextInput::make('monthly_rent')
                                    ->label('Loyer mensuel')
                                    ->numeric()
                                    ->prefix('€')
                                    ->required(),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('deposit_amount')
                                    ->label('Dépôt de garantie')
                                    ->numeric()
                                    ->prefix('€'),
                                
                                Forms\Components\Toggle::make('furnished')
                                    ->label('Meublé')
                                    ->inline(false),
                                
                                Forms\Components\Toggle::make('balcony')
                                    ->label('Balcon')
                                    ->inline(false),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('parking_space')
                                    ->label('Place de parking')
                                    ->inline(false),
                                
                                Forms\Components\Select::make('status')
                                    ->label('Statut')
                                    ->options([
                                        'available' => 'Disponible',
                                        'occupied' => 'Occupé',
                                        'maintenance' => 'En maintenance',
                                        'unavailable' => 'Non disponible',
                                    ])
                                    ->default('available')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Description')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description détaillée')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Équipements')
                    ->schema([
                        Forms\Components\CheckboxList::make('amenities')
                            ->label('Équipements de l\'unité')
                            ->options([
                                'air_conditioning' => 'Climatisation',
                                'heating' => 'Chauffage',
                                'washing_machine' => 'Machine à laver',
                                'dryer' => 'Sèche-linge',
                                'dishwasher' => 'Lave-vaisselle',
                                'internet' => 'Internet haut débit',
                                'tv_cable' => 'TV câblée',
                                'balcony' => 'Balcon',
                                'terrace' => 'Terrasse',
                                'garden' => 'Jardin',
                                'fireplace' => 'Cheminée',
                                'hardwood_floors' => 'Parquet',
                                'tile_floors' => 'Carrelage',
                                'carpet' => 'Moquette',
                                'walk_in_closet' => 'Dressing',
                                'bathtub' => 'Baignoire',
                                'shower' => 'Douche',
                                'double_sink' => 'Double vasque',
                                'kitchen_island' => 'Îlot central',
                                'granite_countertops' => 'Comptoir granit',
                                'stainless_steel_appliances' => 'Appareils inox',
                                'pet_friendly' => 'Animaux acceptés',
                                'smoke_free' => 'Non fumeur',
                            ])
                            ->columns(3),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Médias')
                    ->schema([
                        Forms\Components\FileUpload::make('images')
                            ->label('Photos de l\'unité')
                            ->multiple()
                            ->directory('units/' . date('Y/m'))
                            ->image()
                            ->maxSize(5120) // 5MB
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unit_number')
                    ->label('Numéro')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('building.name')
                    ->label('Immeuble')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('unit_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'studio' => 'info',
                        'apartment' => 'primary',
                        'duplex' => 'warning',
                        'penthouse' => 'danger',
                        'office' => 'success',
                        'retail' => 'gray',
                        'warehouse' => 'dark',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('floor')
                    ->label('Étage')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('area_sqm')
                    ->label('Surface')
                    ->numeric(decimalPlaces: 0)
                    ->suffix(' m²')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('monthly_rent')
                    ->label('Loyer')
                    ->money('EUR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('bedrooms')
                    ->label('Ch.')
                    ->numeric(),
                
                Tables\Columns\TextColumn::make('bathrooms')
                    ->label('SDB')
                    ->numeric(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'occupied' => 'danger',
                        'maintenance' => 'warning',
                        'unavailable' => 'gray',
                        default => 'gray',
                    }),
                
                Tables\Columns\IconColumn::make('furnished')
                    ->label('Meublé')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('balcony')
                    ->label('Balcon')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('parking_space')
                    ->label('Parking')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('currentTenant.full_name')
                    ->label('Locataire actuel')
                    ->placeholder('Aucun'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_type')
                    ->label('Type d\'unité')
                    ->options([
                        'studio' => 'Studio',
                        'apartment' => 'Appartement',
                        'duplex' => 'Duplex',
                        'penthouse' => 'Penthouse',
                        'office' => 'Bureau',
                        'retail' => 'Commerce',
                        'warehouse' => 'Entrepôt',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'available' => 'Disponible',
                        'occupied' => 'Occupé',
                        'maintenance' => 'En maintenance',
                        'unavailable' => 'Non disponible',
                    ]),
                
                Tables\Filters\TernaryFilter::make('furnished')
                    ->label('Meublé'),
                
                Tables\Filters\TernaryFilter::make('balcony')
                    ->label('Avec balcon'),
                
                Tables\Filters\TernaryFilter::make('parking_space')
                    ->label('Avec parking'),
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
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'view' => Pages\ViewUnit::route('/{record}'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}