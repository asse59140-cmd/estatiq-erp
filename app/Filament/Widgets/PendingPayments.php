<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingPayments extends BaseWidget
{
    // On le met en largeur totale sur l'écran
    protected int | string | array $columnSpan = 'full';
    
    // On le place juste en dessous de tes statistiques
    protected static ?int $sort = 2; 

    // Le titre qui claque
    protected static ?string $heading = '🚨 Loyers en attente ou en retard';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // On cherche uniquement les paiements qui ne sont pas "paid" (payés)
                Payment::query()->whereIn('status', ['pending', 'late'])->latest('payment_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('tenant.full_name')
                    ->label('Locataire')
                    ->searchable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('tenant.property.title')
                    ->label('Propriété')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant attendu')
                    ->money('eur')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Date d\'échéance')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'danger' => 'late',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'late' => 'En retard',
                        default => $state,
                    }),
            ])
            ->actions([
                // Un bouton rapide pour aller encaisser le loyer directement depuis l'accueil !
                Tables\Actions\Action::make('encaissement')
                    ->label('Voir / Encaisser')
                    ->url(fn (Payment $record): string => route('filament.admin.resources.payments.edit', ['record' => $record]))
                    ->icon('heroicon-m-pencil-square'),
            ]);
    }
}