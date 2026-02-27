<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    
    protected static ?string $navigationLabel = 'Documents';
    
    protected static ?string $navigationGroup = 'Gestion Documentaire';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations du Document')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom du document')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('document_type')
                            ->label('Type de document')
                            ->options([
                                'contract' => 'Contrat de bail',
                                'invoice' => 'Facture',
                                'receipt' => 'Quittance',
                                'identity' => 'Pièce d\'identité',
                                'proof_of_income' => 'Justificatif de revenus',
                                'guarantor_document' => 'Document de garant',
                                'property_deed' => 'Titre de propriété',
                                'insurance' => 'Assurance',
                                'inspection_report' => 'Rapport d\'inspection',
                                'maintenance_report' => 'Rapport de maintenance',
                                'meter_reading' => 'Relevé de compteur',
                                'other' => 'Autre',
                            ])
                            ->required(),
                        
                        Forms\Components\Select::make('category')
                            ->label('Catégorie')
                            ->options([
                                'legal' => 'Juridique',
                                'financial' => 'Financier',
                                'administrative' => 'Administratif',
                                'technical' => 'Technique',
                                'personal' => 'Personnel',
                                'other' => 'Autre',
                            ])
                            ->nullable(),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\TagsInput::make('tags')
                            ->label('Tags')
                            ->placeholder('Ajouter un tag'),
                    ])
                    ->columns(2),

                Section::make('Fichier')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('Document')
                            ->directory('documents/' . date('Y/m'))
                            ->preserveFilenames()
                            ->maxSize(10240) // 10MB
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'image/jpeg',
                                'image/png',
                                'image/jpg',
                                'text/plain',
                            ])
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Expiration et Version')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\DatePicker::make('expires_at')
                                    ->label('Date d\'expiration')
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),
                                
                                Forms\Components\TextInput::make('version')
                                    ->label('Version')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1),
                                
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Actif')
                                    ->default(true)
                                    ->inline(false),
                            ]),
                    ]),

                Section::make('Entité Associée')
                    ->schema([
                        Forms\Components\Select::make('documentable_type')
                            ->label('Type d\'entité')
                            ->options([
                                'App\\Models\\Building' => 'Immeuble',
                                'App\\Models\\Unit' => 'Unité',
                                'App\\Models\\Tenant' => 'Locataire',
                                'App\\Models\\Owner' => 'Propriétaire',
                                'App\\Models\\Agency' => 'Agence',
                            ])
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('documentable_id', null)),
                        
                        Forms\Components\Select::make('documentable_id')
                            ->label('Entité')
                            ->options(function (callable $get) {
                                $type = $get('documentable_type');
                                if (!$type) {
                                    return [];
                                }
                                
                                return $type::where('agency_id', auth()->user()->agencies()->first()->id)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(fn (callable $get) => !$get('documentable_type')),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
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
                
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'contract' => 'success',
                        'invoice' => 'warning',
                        'receipt' => 'info',
                        'identity' => 'primary',
                        'property_deed' => 'danger',
                        'insurance' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'contract' => 'Contrat',
                        'invoice' => 'Facture',
                        'receipt' => 'Quittance',
                        'identity' => 'ID',
                        'proof_of_income' => 'Revenus',
                        'guarantor_document' => 'Garant',
                        'property_deed' => 'Titre',
                        'insurance' => 'Assurance',
                        'inspection_report' => 'Inspection',
                        'maintenance_report' => 'Maintenance',
                        'meter_reading' => 'Compteur',
                        'other' => 'Autre',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'legal' => 'danger',
                        'financial' => 'warning',
                        'administrative' => 'info',
                        'technical' => 'success',
                        'personal' => 'primary',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('file_size_human_readable')
                    ->label('Taille')
                    ->sortable()
                    ->sortColumn('file_size'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expiration')
                    ->date()
                    ->sortable()
                    ->color(fn ($state) => 
                        $state && $state->isPast() ? 'danger' : 
                        ($state && $state->diffInDays() <= 30 ? 'warning' : null)
                    ),
                
                Tables\Columns\TextColumn::make('documentable_type')
                    ->label('Associé à')
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'App\\Models\\Building' => 'Immeuble',
                            'App\\Models\\Unit' => 'Unité',
                            'App\\Models\\Tenant' => 'Locataire',
                            'App\\Models\\Owner' => 'Propriétaire',
                            'App\\Models\\Agency' => 'Agence',
                            default => 'Aucun',
                        };
                    }),
                
                Tables\Columns\TextColumn::make('version')
                    ->label('v.')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->label('Type de document')
                    ->options([
                        'contract' => 'Contrat de bail',
                        'invoice' => 'Facture',
                        'receipt' => 'Quittance',
                        'identity' => 'Pièce d\'identité',
                        'proof_of_income' => 'Justificatif de revenus',
                        'guarantor_document' => 'Document de garant',
                        'property_deed' => 'Titre de propriété',
                        'insurance' => 'Assurance',
                        'inspection_report' => 'Rapport d\'inspection',
                        'maintenance_report' => 'Rapport de maintenance',
                        'meter_reading' => 'Relevé de compteur',
                        'other' => 'Autre',
                    ]),
                
                Tables\Filters\SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options([
                        'legal' => 'Juridique',
                        'financial' => 'Financier',
                        'administrative' => 'Administratif',
                        'technical' => 'Technique',
                        'personal' => 'Personnel',
                        'other' => 'Autre',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Actif'),
                
                Tables\Filters\Filter::make('expires_soon')
                    ->label('Expire bientôt')
                    ->query(fn ($query) => $query->expiringSoon()),
                
                Tables\Filters\Filter::make('expired')
                    ->label('Expiré')
                    ->query(fn ($query) => $query->expired()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Document $record) {
                        return response()->download(Storage::path($record->file_path), $record->name);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_inactive')
                        ->label('Marquer comme inactif')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
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
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'view' => Pages\ViewDocument::route('/{record}'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}