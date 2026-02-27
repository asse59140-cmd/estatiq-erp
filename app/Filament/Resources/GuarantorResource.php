<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuarantorResource\Pages;
use App\Models\Guarantor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class GuarantorResource extends Resource
{
    protected static ?string $model = Guarantor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = 'Garants';
    
    protected static ?string $navigationGroup = 'Gestion Locative';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations Personnelles')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('last_name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Date de naissance')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->maxDate(now()->subYears(18)),
                        
                        Forms\Components\Select::make('gender')
                            ->label('Genre')
                            ->options([
                                'male' => 'Masculin',
                                'female' => 'Féminin',
                                'other' => 'Autre',
                            ])
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('nationality')
                            ->label('Nationalité')
                            ->placeholder('Française'),
                        
                        Forms\Components\TextInput::make('profession')
                            ->label('Profession')
                            ->placeholder('Ingénieur, Comptable...'),
                        
                        Forms\Components\TextInput::make('monthly_income')
                            ->label('Revenus mensuels')
                            ->numeric()
                            ->prefix('€')
                            ->placeholder('2500'),
                    ])
                    ->columns(2),

                Section::make('Contact')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->placeholder('jean.dupont@email.com'),
                        
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->placeholder('+33 6 12 34 56 78'),
                        
                        Forms\Components\Textarea::make('address')
                            ->label('Adresse')
                            ->rows(2)
                            ->columnSpanFull(),
                        
                        Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('city')
                                    ->label('Ville'),
                                
                                Forms\Components\TextInput::make('postal_code')
                                    ->label('Code postal')
                                    ->maxLength(10),
                                
                                Forms\Components\TextInput::make('country')
                                    ->label('Pays')
                                    ->default('France'),
                            ]),
                    ]),

                Section::make('Pièce d\'identité')
                    ->schema([
                        Forms\Components\Select::make('id_type')
                            ->label('Type de pièce')
                            ->options([
                                'passport' => 'Passeport',
                                'national_id' => 'Carte nationale d\'identité',
                                'driver_license' => 'Permis de conduire',
                                'other' => 'Autre',
                            ])
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('id_number')
                            ->label('Numéro de pièce')
                            ->placeholder('123456789'),
                    ])
                    ->columns(2),

                Section::make('Garantie')
                    ->schema([
                        Forms\Components\TextInput::make('relationship_to_tenant')
                            ->label('Relation avec le locataire')
                            ->placeholder('Père, Mère, Ami, Collègue...'),
                        
                        Forms\Components\TextInput::make('guarantee_amount')
                            ->label('Montant de la garantie')
                            ->numeric()
                            ->prefix('€')
                            ->placeholder('3000'),
                        
                        Forms\Components\Select::make('guarantee_type')
                            ->label('Type de garantie')
                            ->options([
                                'full' => 'Garantie totale',
                                'partial' => 'Garantie partielle',
                                'limited' => 'Garantie limitée',
                            ])
                            ->default('full')
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Statut et Vérification')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'active' => 'Actif',
                                'inactive' => 'Inactif',
                                'suspended' => 'Suspendu',
                            ])
                            ->default('active')
                            ->required(),
                        
                        Forms\Components\Toggle::make('documents_verified')
                            ->label('Documents vérifiés')
                            ->inline(false),
                        
                        Forms\Components\DatePicker::make('verified_at')
                            ->label('Date de vérification')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->visible(fn (callable $get) => $get('documents_verified')),
                        
                        Forms\Components\Select::make('verified_by')
                            ->label('Vérifié par')
                            ->relationship('verifiedBy', 'first_name', fn($query) => 
                                $query->where('agency_id', auth()->user()->agencies()->first()->id)
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                            ->searchable()
                            ->preload()
                            ->visible(fn (callable $get) => $get('documents_verified')),
                    ])
                    ->columns(4),

                Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes et observations')
                            ->rows(4)
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
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nom complet')
                    ->getStateUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['last_name', 'first_name']),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->icon('heroicon-m-envelope'),
                
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('profession')
                    ->label('Profession')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('monthly_income')
                    ->label('Revenus')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('documents_verified')
                    ->label('Vérifié')
                    ->boolean()
                    ->color(fn ($state) => $state ? 'success' : 'warning'),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'suspended' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        'suspended' => 'Suspendu',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('active_tenants_count')
                    ->label('Locataires')
                    ->getStateUsing(fn ($record) => $record->active_tenants_count)
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Date de vérification')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        'suspended' => 'Suspendu',
                    ]),
                
                Tables\Filters\TernaryFilter::make('documents_verified')
                    ->label('Documents vérifiés'),
                
                Tables\Filters\Filter::make('high_income')
                    ->label('Revenus élevés')
                    ->query(fn ($query) => $query->where('monthly_income', '>', 3000)),
                
                Tables\Filters\Filter::make('verified')
                    ->label('Vérifiés')
                    ->query(fn ($query) => $query->verified()),
                
                Tables\Filters\Filter::make('pending_verification')
                    ->label('En attente de vérification')
                    ->query(fn ($query) => $query->pendingVerification()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('verify')
                    ->label('Vérifier')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => !$record->is_verified)
                    ->action(fn ($record) => $record->update([
                        'documents_verified' => true,
                        'verified_at' => now(),
                        'verified_by' => auth()->id(),
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('verify_documents')
                        ->label('Vérifier les documents')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => 
                            $records->each->update([
                                'documents_verified' => true,
                                'verified_at' => now(),
                                'verified_by' => auth()->id(),
                            ])
                        ),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Modifier le statut')
                        ->icon('heroicon-o-pencil')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Nouveau statut')
                                ->options([
                                    'active' => 'Actif',
                                    'inactive' => 'Inactif',
                                    'suspended' => 'Suspendu',
                                ])
                                ->required(),
                        ])
                        ->action(fn ($records, $data) => $records->each->update(['status' => $data['status']])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Relations à ajouter : TenantRelationManager, DocumentRelationManager
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuarantors::route('/'),
            'create' => Pages\CreateGuarantor::route('/create'),
            'view' => Pages\ViewGuarantor::route('/{record}'),
            'edit' => Pages\EditGuarantor::route('/{record}/edit'),
        ];
    }
}