<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientPortalResource\Pages;
use App\Models\ClientPortal;
use App\Models\Tenant;
use App\Models\Owner;
use App\Models\Guarantor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class ClientPortalResource extends Resource
{
    protected static ?string $model = ClientPortal::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    
    protected static ?string $navigationLabel = 'Portails Clients';
    
    protected static ?string $navigationGroup = 'Portail Client';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations du Portail')
                    ->schema([
                        Forms\Components\Select::make('client_type')
                            ->label('Type de client')
                            ->options([
                                'App\Models\Tenant' => 'Locataire',
                                'App\Models\Owner' => 'Propriétaire',
                                'App\Models\Guarantor' => 'Garant',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('client_id', null)),
                        
                        Forms\Components\Select::make('client_id')
                            ->label('Client')
                            ->options(function (callable $get) {
                                $clientType = $get('client_type');
                                if (!$clientType) {
                                    return [];
                                }
                                
                                return $clientType::where('agency_id', auth()->user()->agencies()->first()->id)
                                    ->get()
                                    ->mapWithKeys(function ($client) {
                                        return [$client->id => $client->full_name ?? $client->name ?? $client->email];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn (callable $get) => !$get('client_type')),
                        
                        Forms\Components\Select::make('portal_type')
                            ->label('Type de portail')
                            ->options([
                                'tenant' => 'Portail Locataire',
                                'owner' => 'Portail Propriétaire',
                                'guarantor' => 'Portail Garant',
                                'both' => 'Portail Complet',
                            ])
                            ->default('tenant')
                            ->required(),
                        
                        Forms\Components\TextInput::make('access_code')
                            ->label('Code d\'accès')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => strtoupper(bin2hex(random_bytes(4))))
                            ->helperText('Code à communiquer au client pour accéder au portail'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),

                Section::make('Configuration Avancée')
                    ->schema([
                        Forms\Components\TextInput::make('api_token')
                            ->label('Token API')
                            ->disabled()
                            ->helperText('Token pour accès API (généré automatiquement)'),
                        
                        Forms\Components\DateTimePicker::make('token_expires_at')
                            ->label('Expiration du token')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->helperText('Laisser vide pour une expiration dans 30 jours'),
                        
                        Forms\Components\DateTimePicker::make('last_login_at')
                            ->label('Dernière connexion')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('login_count')
                            ->label('Nombre de connexions')
                            ->numeric()
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                Section::make('Préférences')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Toggle::make('preferences.notifications_email')
                                    ->label('Notifications par email')
                                    ->default(true)
                                    ->inline(false),
                                
                                Forms\Components\Toggle::make('preferences.notifications_whatsapp')
                                    ->label('Notifications WhatsApp')
                                    ->default(true)
                                    ->inline(false),
                                
                                Forms\Components\Toggle::make('preferences.notifications_sms')
                                    ->label('Notifications SMS')
                                    ->default(false)
                                    ->inline(false),
                            ]),
                        
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('preferences.auto_payment_reminders')
                                    ->label('Rappels de paiement automatiques')
                                    ->default(true)
                                    ->inline(false),
                                
                                Forms\Components\Toggle::make('preferences.monthly_receipts')
                                    ->label('Quittances mensuelles automatiques')
                                    ->default(true)
                                    ->inline(false),
                            ]),
                        
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('preferences.language')
                                    ->label('Langue')
                                    ->options([
                                        'fr' => 'Français',
                                        'ar' => 'العربية',
                                        'en' => 'English',
                                    ])
                                    ->default('fr')
                                    ->required(),
                                
                                Forms\Components\Select::make('preferences.timezone')
                                    ->label('Fuseau horaire')
                                    ->options([
                                        'Europe/Paris' => 'Europe/Paris',
                                        'Africa/Casablanca' => 'Afrique/Casablanca',
                                        'Asia/Dubai' => 'Asie/Dubai',
                                        'UTC' => 'UTC',
                                    ])
                                    ->default('Europe/Paris')
                                    ->required(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Client')
                    ->getStateUsing(fn ($record) => $record->client_name)
                    ->searchable(['client_type', 'client_id'])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('portal_type')
                    ->label('Type de portail')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tenant' => 'info',
                        'owner' => 'success',
                        'guarantor' => 'warning',
                        'both' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'tenant' => 'Locataire',
                        'owner' => 'Propriétaire',
                        'guarantor' => 'Garant',
                        'both' => 'Complet',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('access_code')
                    ->label('Code d\'accès')
                    ->badge()
                    ->copyable()
                    ->copyMessage('Code copié !')
                    ->copyMessageDuration(1500),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Statut')
                    ->boolean()
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                
                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Dernière connexion')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Jamais'),
                
                Tables\Columns\TextColumn::make('login_count')
                    ->label('Connexions')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur lié')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('portal_type')
                    ->label('Type de portail')
                    ->options([
                        'tenant' => 'Locataire',
                        'owner' => 'Propriétaire',
                        'guarantor' => 'Garant',
                        'both' => 'Complet',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Actif'),
                
                Tables\Filters\Filter::make('recently_logged_in')
                    ->label('Récemment connecté')
                    ->query(fn ($query) => $query->where('last_login_at', '>=', now()->subDays(30))),
                
                Tables\Filters\Filter::make('never_logged_in')
                    ->label('Jamais connecté')
                    ->query(fn ($query) => $query->whereNull('last_login_at')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('generate_new_code')
                    ->label('Nouveau code')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->action(function ($record) {
                        $newCode = strtoupper(bin2hex(random_bytes(4)));
                        $record->update(['access_code' => $newCode]);
                        
                        Notification::make()
                            ->title('Nouveau code généré')
                            ->body("Le nouveau code d'accès est : {$newCode}")
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('generate_api_token')
                    ->label('Générer token')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('info')
                    ->action(function ($record) {
                        $newToken = bin2hex(random_bytes(32));
                        $record->update([
                            'api_token' => $newToken,
                            'token_expires_at' => now()->addDays(30)
                        ]);
                        
                        Notification::make()
                            ->title('Token API généré')
                            ->body("Le nouveau token expire le {$record->token_expires_at->format('d/m/Y')}")
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('revoke_access')
                    ->label('Révoquer')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn ($record) => $record->is_active)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['is_active' => false]);
                        
                        Notification::make()
                            ->title('Accès révoqué')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('restore_access')
                    ->label('Restaurer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => !$record->is_active)
                    ->action(function ($record) {
                        $record->update(['is_active' => true]);
                        
                        Notification::make()
                            ->title('Accès restauré')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('generate_new_codes')
                        ->label('Générer nouveaux codes')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->action(fn ($records) => 
                            $records->each(function ($record) {
                                $newCode = strtoupper(bin2hex(random_bytes(4)));
                                $record->update(['access_code' => $newCode]);
                            })
                        ),
                    Tables\Actions\BulkAction::make('revoke_access')
                        ->label('Révoquer accès')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => 
                            $records->each->update(['is_active' => false])
                        ),
                    Tables\Actions\BulkAction::make('restore_access')
                        ->label('Restaurer accès')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => 
                            $records->each->update(['is_active' => true])
                        ),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Relations à ajouter : PortalActivityRelationManager, PortalPaymentRelationManager, etc.
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientPortals::route('/'),
            'create' => Pages\CreateClientPortal::route('/create'),
            'view' => Pages\ViewClientPortal::route('/{record}'),
            'edit' => Pages\EditClientPortal::route('/{record}/edit'),
        ];
    }
}