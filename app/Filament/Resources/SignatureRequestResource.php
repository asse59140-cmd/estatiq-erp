<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SignatureRequestResource\Pages;
use App\Models\SignatureRequest;
use App\Services\ElectronicSignatureService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class SignatureRequestResource extends Resource
{
    protected static ?string $model = SignatureRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    
    protected static ?string $navigationLabel = 'Demandes de Signature';
    
    protected static ?string $navigationGroup = 'Signature Électronique';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations de la Demande')
                    ->schema([
                        Forms\Components\Select::make('document_id')
                            ->label('Document à signer')
                            ->relationship('document', 'name', fn($query) => 
                                $query->where('agency_id', auth()->user()->agencies()->first()->id)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive(),
                        
                        Forms\Components\Select::make('request_type')
                            ->label('Type de demande')
                            ->options([
                                'lease_contract' => 'Contrat de bail',
                                'invoice' => 'Facture',
                                'maintenance_contract' => 'Contrat de maintenance',
                                'property_purchase' => 'Achat de propriété',
                                'guarantee_agreement' => 'Convention de garantie',
                                'other' => 'Autre',
                            ])
                            ->default('lease_contract')
                            ->required(),
                        
                        Forms\Components\TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('provider')
                            ->label('Fournisseur')
                            ->options(function () {
                                return \App\Services\ElectronicSignatureService::getAvailableProviders();
                            })
                            ->default('docusign')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Signataires')
                    ->schema([
                        Repeater::make('signers')
                            ->label('Signataires')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom complet')
                                    ->required()
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('phone')
                                    ->label('Téléphone')
                                    ->tel()
                                    ->maxLength(20),
                                
                                Forms\Components\Select::make('role')
                                    ->label('Rôle')
                                    ->options([
                                        'tenant' => 'Locataire',
                                        'owner' => 'Propriétaire',
                                        'guarantor' => 'Garant',
                                        'agent' => 'Agent',
                                        'witness' => 'Témoin',
                                        'other' => 'Autre',
                                    ])
                                    ->default('tenant')
                                    ->required(),
                                
                                Forms\Components\TextInput::make('order')
                                    ->label('Ordre')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1),
                                
                                Forms\Components\TextInput::make('page')
                                    ->label('Page')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1),
                                
                                Forms\Components\TextInput::make('x')
                                    ->label('Position X')
                                    ->numeric()
                                    ->default(100),
                                
                                Forms\Components\TextInput::make('y')
                                    ->label('Position Y')
                                    ->numeric()
                                    ->default(100),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->minItems(1)
                            ->required(),
                    ]),

                Section::make('Configuration')
                    ->schema([
                        Forms\Components\DatePicker::make('expired_date')
                            ->label('Date d\'expiration')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now()->addDays(30))
                            ->required(),
                        
                        Forms\Components\Toggle::make('auto_send')
                            ->label('Envoi automatique')
                            ->default(true)
                            ->inline(false),
                        
                        Forms\Components\TextInput::make('metadata.reminder_days')
                            ->label('Jours entre relances')
                            ->numeric()
                            ->default(3)
                            ->minValue(1)
                            ->maxValue(30),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('document.name')
                    ->label('Document')
                    ->searchable()
                    ->icon('heroicon-m-document-text'),
                
                Tables\Columns\TextColumn::make('request_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'lease_contract' => 'success',
                        'invoice' => 'warning',
                        'maintenance_contract' => 'info',
                        'property_purchase' => 'danger',
                        'guarantee_agreement' => 'primary',
                        'other' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'lease_contract' => 'Contrat de bail',
                        'invoice' => 'Facture',
                        'maintenance_contract' => 'Maintenance',
                        'property_purchase' => 'Achat',
                        'guarantee_agreement' => 'Garantie',
                        'other' => 'Autre',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('provider')
                    ->label('Fournisseur')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'docusign' => 'blue',
                        'dropbox_sign' => 'red',
                        'adobe_sign' => 'purple',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'sent' => 'info',
                        'viewed' => 'primary',
                        'completed' => 'success',
                        'expired' => 'danger',
                        'cancelled' => 'gray',
                        'declined' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'sent' => 'Envoyé',
                        'viewed' => 'Consulté',
                        'completed' => 'Terminé',
                        'expired' => 'Expiré',
                        'cancelled' => 'Annulé',
                        'declined' => 'Refusé',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('progress')
                    ->label('Progression')
                    ->getStateUsing(fn ($record) => $record->progress . '%')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 100 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),
                
                Tables\Columns\TextColumn::make('signers_count')
                    ->label('Signataires')
                    ->getStateUsing(fn ($record) => count($record->signers ?? [])),
                
                Tables\Columns\TextColumn::make('completed_signers_count')
                    ->label('Signés')
                    ->getStateUsing(fn ($record) => count($record->completed_signers)),
                
                Tables\Columns\TextColumn::make('expired_date')
                    ->label('Expiration')
                    ->date()
                    ->sortable()
                    ->color(fn ($state) => 
                        $state && $state->isPast() ? 'danger' : 
                        ($state && $state->diffInDays() <= 7 ? 'warning' : null)
                    ),
                
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
                        'pending' => 'En attente',
                        'sent' => 'Envoyé',
                        'viewed' => 'Consulté',
                        'completed' => 'Terminé',
                        'expired' => 'Expiré',
                        'cancelled' => 'Annulé',
                        'declined' => 'Refusé',
                    ]),
                
                Tables\Filters\SelectFilter::make('provider')
                    ->label('Fournisseur')
                    ->options(function () {
                        return \App\Services\ElectronicSignatureService::getAvailableProviders();
                    }),
                
                Tables\Filters\SelectFilter::make('request_type')
                    ->label('Type de demande')
                    ->options([
                        'lease_contract' => 'Contrat de bail',
                        'invoice' => 'Facture',
                        'maintenance_contract' => 'Contrat de maintenance',
                        'property_purchase' => 'Achat de propriété',
                        'guarantee_agreement' => 'Convention de garantie',
                        'other' => 'Autre',
                    ]),
                
                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Expire bientôt')
                    ->query(fn ($query) => $query->expiringSoon()),
                
                Tables\Filters\Filter::make('completed')
                    ->label('Terminés')
                    ->query(fn ($query) => $query->completed()),
                
                Tables\Filters\Filter::make('pending')
                    ->label('En attente')
                    ->query(fn ($query) => $query->pending()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('send')
                    ->label('Envoyer')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        try {
                            $service = new ElectronicSignatureService($record->provider);
                            $result = $service->createLeaseContractEnvelope(
                                $record->requestable,
                                $record->signers,
                                $record->document
                            );
                            
                            $record->update([
                                'envelope_id' => $result['envelope_id'],
                                'status' => 'sent',
                                'sent_date' => now(),
                            ]);
                            
                            Notification::make()
                                ->title('Demande envoyée')
                                ->body('La demande de signature a été envoyée avec succès.')
                                ->success()
                                ->send();
                                
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur d\'envoi')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
                Tables\Actions\Action::make('download_signed')
                    ->label('Télécharger signé')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->visible(fn ($record) => $record->status === 'completed' && $record->signed_document_path)
                    ->action(function ($record) {
                        return response()->download(Storage::path($record->signed_document_path));
                    }),
                
                Tables\Actions\Action::make('cancel')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->canBeCancelled())
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'cancelled']);
                        
                        Notification::make()
                            ->title('Demande annulée')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('remind')
                    ->label('Relancer')
                    ->icon('heroicon-o-bell')
                    ->color('warning')
                    ->visible(fn ($record) => $record->canBeReminded())
                    ->action(function ($record) {
                        // Logique de relance à implémenter
                        $record->update(['reminder_sent_at' => now()]);
                        
                        Notification::make()
                            ->title('Relance envoyée')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('send_requests')
                        ->label('Envoyer les demandes')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->action(function ($records) {
                            $sent = 0;
                            foreach ($records as $record) {
                                if ($record->status === 'pending') {
                                    try {
                                        $service = new ElectronicSignatureService($record->provider);
                                        $result = $service->createLeaseContractEnvelope(
                                            $record->requestable,
                                            $record->signers,
                                            $record->document
                                        );
                                        
                                        $record->update([
                                            'envelope_id' => $result['envelope_id'],
                                            'status' => 'sent',
                                            'sent_date' => now(),
                                        ]);
                                        
                                        $sent++;
                                    } catch (\Exception $e) {
                                        // Log l'erreur mais continue avec les autres
                                    }
                                }
                            }
                            
                            Notification::make()
                                ->title("{$sent} demandes envoyées")
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\BulkAction::make('cancel_requests')
                        ->label('Annuler les demandes')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => 
                            $records->each->update(['status' => 'cancelled'])
                        ),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Relations à ajouter : SignerRelationManager, DocumentRelationManager
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSignatureRequests::route('/'),
            'create' => Pages\CreateSignatureRequest::route('/create'),
            'view' => Pages\ViewSignatureRequest::route('/{record}'),
            'edit' => Pages\EditSignatureRequest::route('/{record}/edit'),
        ];
    }
}