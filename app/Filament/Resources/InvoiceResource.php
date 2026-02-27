<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\Owner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Factures';
    
    protected static ?string $navigationGroup = 'Facturation';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations de la Facture')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Numéro de facture')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'FA-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT)),
                        
                        Forms\Components\TextInput::make('reference')
                            ->label('Référence client')
                            ->placeholder('Contrat n°...'),
                        
                        Forms\Components\Select::make('client_type')
                            ->label('Type de client')
                            ->options([
                                'App\Models\Tenant' => 'Locataire',
                                'App\Models\Owner' => 'Propriétaire',
                                'App\Models\Agency' => 'Agence',
                            ])
                            ->required()
                            ->reactive(),
                        
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
                    ])
                    ->columns(2),

                Section::make('Dates et Conditions')
                    ->schema([
                        Forms\Components\DatePicker::make('issue_date')
                            ->label('Date d\'émission')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now())
                            ->required(),
                        
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Date d\'échéance')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now()->addDays(30))
                            ->afterOrEqual('issue_date')
                            ->required(),
                        
                        Forms\Components\TextInput::make('payment_terms')
                            ->label('Conditions de paiement')
                            ->default('30 jours')
                            ->placeholder('30 jours, À réception, etc.'),
                        
                        Forms\Components\Select::make('currency')
                            ->label('Devise')
                            ->options([
                                'EUR' => 'Euro (€)',
                                'USD' => 'Dollar américain ($)',
                                'MAD' => 'Dirham marocain (MAD)',
                            ])
                            ->default('EUR')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Articles')
                    ->schema([
                        Repeater::make('items')
                            ->label('Lignes de facture')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->rows(2)
                                    ->required()
                                    ->columnSpan(2),
                                
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Quantité')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => 
                                        $set('total_price', $state * $get('unit_price'))
                                    ),
                                
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Prix unitaire')
                                    ->numeric()
                                    ->prefix('€')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => 
                                        $set('total_price', $state * $get('quantity'))
                                    ),
                                
                                Forms\Components\TextInput::make('tax_rate')
                                    ->label('TVA (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(20)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => 
                                        $set('tax_amount', ($get('quantity') * $get('unit_price') * $state) / 100)
                                    ),
                                
                                Forms\Components\TextInput::make('tax_amount')
                                    ->label('Montant TVA')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled(),
                                
                                Forms\Components\TextInput::make('total_price')
                                    ->label('Total TTC')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled(),
                            ])
                            ->columns(6)
                            ->columnSpanFull()
                            ->defaultItems(1)
                            ->cloneable()
                            ->collapsible(),
                    ]),

                Section::make('Résumé')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Sous-total')
                            ->numeric()
                            ->prefix('€')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('tax_amount')
                            ->label('Total TVA')
                            ->numeric()
                            ->prefix('€')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('discount_amount')
                            ->label('Remise')
                            ->numeric()
                            ->prefix('€')
                            ->default(0),
                        
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total TTC')
                            ->numeric()
                            ->prefix('€')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('paid_amount')
                            ->label('Montant payé')
                            ->numeric()
                            ->prefix('€')
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('balance_due')
                            ->label('Solde dû')
                            ->numeric()
                            ->prefix('€')
                            ->disabled(),
                    ])
                    ->columns(3),

                Section::make('Pénalités de retard')
                    ->schema([
                        Forms\Components\TextInput::make('late_fee_percentage')
                            ->label('Taux de pénalité (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(1.5)
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Notes et conditions')
                    ->schema([
                        Forms\Components\RichEditor::make('notes')
                            ->label('Notes')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ]),
                        
                        Forms\Components\RichEditor::make('terms_and_conditions')
                            ->label('Conditions générales')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
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
                Tables\Columns\TextColumn::make('formatted_invoice_number')
                    ->label('N° Facture')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Client')
                    ->getStateUsing(function ($record) {
                        $client = $record->client;
                        return $client ? ($client->full_name ?? $client->name ?? 'Client') : 'N/A';
                    })
                    ->searchable(['client_type', 'client_id']),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Montant')
                    ->money('EUR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('balance_due')
                    ->label('Solde dû')
                    ->money('EUR')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'viewed' => 'warning',
                        'paid' => 'success',
                        'partially_paid' => 'warning',
                        'overdue' => 'danger',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'sent' => 'Envoyée',
                        'viewed' => 'Consultée',
                        'paid' => 'Payée',
                        'partially_paid' => 'Partiellement payée',
                        'overdue' => 'En retard',
                        'cancelled' => 'Annulée',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('issue_date')
                    ->label('Date d\'émission')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Date d\'échéance')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => 
                        $record->is_overdue ? 'danger' : 
                        ($record->due_date->diffInDays() <= 7 ? 'warning' : null)
                    ),
                
                Tables\Columns\TextColumn::make('days_overdue')
                    ->label('Jours de retard')
                    ->numeric()
                    ->color('danger')
                    ->visible(fn ($record) => $record->is_overdue),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft' => 'Brouillon',
                        'sent' => 'Envoyée',
                        'viewed' => 'Consultée',
                        'paid' => 'Payée',
                        'partially_paid' => 'Partiellement payée',
                        'overdue' => 'En retard',
                        'cancelled' => 'Annulée',
                    ]),
                
                Tables\Filters\Filter::make('overdue')
                    ->label('En retard')
                    ->query(fn ($query) => $query->overdue()),
                
                Tables\Filters\Filter::make('unpaid')
                    ->label('Non payées')
                    ->query(fn ($query) => $query->whereNotIn('status', ['paid', 'cancelled'])),
                
                Tables\Filters\Filter::make('date_range')
                    ->label('Période')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Du'),
                        Forms\Components\DatePicker::make('to')
                            ->label('Au'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('issue_date', '>=', $date))
                            ->when($data['to'], fn ($q, $date) => $q->whereDate('issue_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($record) {
                        // Logique de téléchargement PDF
                        return response()->download(
                            storage_path('app/invoices/' . $record->invoice_number . '.pdf'),
                            'facture-' . $record->invoice_number . '.pdf'
                        );
                    }),
                Tables\Actions\Action::make('record_payment')
                    ->label('Enregistrer paiement')
                    ->icon('heroicon-o-currency-euro')
                    ->color('primary')
                    ->visible(fn ($record) => !$record->is_paid)
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Montant')
                            ->numeric()
                            ->required()
                            ->maxValue(fn ($record) => $record->balance_due),
                        Forms\Components\Select::make('payment_method')
                            ->label('Méthode de paiement')
                            ->options([
                                'cash' => 'Espèces',
                                'check' => 'Chèque',
                                'bank_transfer' => 'Virement bancaire',
                                'credit_card' => 'Carte de crédit',
                                'online' => 'Paiement en ligne',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('reference')
                            ->label('Référence')
                            ->placeholder('N° de chèque, référence virement...'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        $record->recordPayment($data['amount'], $data['payment_method'], [
                            'reference' => $data['reference'],
                            'notes' => $data['notes'],
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_sent')
                        ->label('Marquer comme envoyées')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->action(fn ($records) => $records->each->update(['status' => 'sent'])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Relations à ajouter : InvoiceItemsRelationManager, InvoicePaymentsRelationManager
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}