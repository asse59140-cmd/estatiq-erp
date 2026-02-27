<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    // Icône de billet pour la finance
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    // On le range dans le bon menu
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $modelLabel = 'Dépense';
    protected static ?string $pluralModelLabel = 'Dépenses';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('property_id')
                    ->relationship('property', 'title') // Adapte 'title' par 'name' si besoin selon ton modèle Property
                    ->label('Bien immobilier')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                Forms\Components\TextInput::make('amount')
                    ->label('Montant')
                    ->numeric()
                    ->prefix('€')
                    ->required(),
                
                Forms\Components\TextInput::make('description')
                    ->label('Description')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\DatePicker::make('expense_date')
                    ->label('Date de la dépense')
                    ->default(now())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('property.title')
                    ->label('Bien')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->money('eur') // Affichage en Euros
                    ->sortable()
                    ->color('danger') // Les dépenses s'affichent en rouge
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('expense_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                // On pourra ajouter des filtres par date plus tard
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

    public static function getPages(): array
    {
        // En mode Modal, on n'a besoin que de la page "Manage"
        return [
            'index' => Pages\ManageExpenses::route('/'),
        ];
    }
}