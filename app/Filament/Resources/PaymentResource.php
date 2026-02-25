<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-euro';
    
    protected static ?string $navigationLabel = 'Paiements';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Détails du Paiement')
                    ->schema([
                        Forms\Components\Select::make('tenant_id')
                            ->label('Locataire')
                            ->relationship('tenant', 'full_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Montant attendu/encaissé')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Date de règlement (ou d\'échéance)')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('method')
                            ->label('Mode de paiement')
                            ->options([
                                'virement' => 'Virement',
                                'especes' => 'Espèces',
                                'cheque' => 'Chèque',
                            ])
                            ->required(),
                        // LE VOICI ! Le champ manquant pour le statut :
                        Forms\Components\Select::make('status')
                            ->label('Statut du paiement')
                            ->options([
                                'paid' => 'Payé',
                                'pending' => 'En attente',
                                'late' => 'En retard',
                            ])
                            ->default('paid')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.full_name')
                    ->label('Locataire')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->money('eur')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'pending',
                        'danger' => 'late',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'Payé',
                        'pending' => 'En attente',
                        'late' => 'En retard',
                        default => $state,
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // ACTION POUR LE PDF
                Tables\Actions\Action::make('downloadPdf')
                    ->label('Quittance')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (Payment $record) {
                        $pdf = Pdf::loadView('pdf.receipt', ['payment' => $record]);
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, "Quittance_Loyer_{$record->id}.pdf");
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}