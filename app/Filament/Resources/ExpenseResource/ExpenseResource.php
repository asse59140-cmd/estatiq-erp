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

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $navigationLabel = 'Dépenses';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->label('Montant')
                    ->prefix('DH'),
                
                Forms\Components\Select::make('property_id')
                    ->relationship('property', 'title') // Assure-toi que la relation 'property' existe dans ton modèle Expense
                    ->required()
                    ->label('Bien immobilier'),

                Forms\Components\DatePicker::make('date')
                    ->required()
                    ->default(now())
                    ->label('Date de la dépense'),

                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255)
                    ->label('Description'),

                Forms\Components\Select::make('category')
                    ->options([
                        'maintenance' => 'Maintenance',
                        'tax' => 'Taxe / Impôts',
                        'insurance' => 'Assurance',
                        'utility' => 'Services (Eau/Elec)',
                        'other' => 'Autre',
                    ])
                    ->required()
                    ->label('Catégorie'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable()
                    ->label('Date'),

                Tables\Columns\TextColumn::make('amount')
                    ->money('mad')
                    ->sortable()
                    ->label('Montant'),

                Tables\Columns\TextColumn::make('property.title')
                    ->label('Bien immobilier')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary' => 'maintenance',
                        'success' => 'utility',
                        'warning' => 'tax',
                        'danger' => 'insurance',
                    ])
                    ->label('Catégorie'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            // C'est ici que nous avons corrigé ListExpenses en ManageExpenses
            'index' => Pages\ManageExpenses::route('/'),
        ];
    }
}