<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class ExpenseResource extends Resource {
    protected static ?string $model = Expense::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Dépenses';

    public static function form(Forms\Form $form): Forms\Form {
        return $form->schema([
            Forms\Components\TextInput::make('category')->label('Catégorie (ex: Plomberie, Taxe)')->required(),
            Forms\Components\TextInput::make('amount')->numeric()->prefix('€')->required(),
            Forms\Components\DatePicker::make('expense_date')->label('Date')->default(now())->required(),
            Forms\Components\Select::make('property_id')->relationship('property', 'title')->label('Lié au bien (Optionnel)'),
            Forms\Components\Textarea::make('note')->columnSpanFull(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('expense_date')->label('Date')->date()->sortable(),
            Tables\Columns\TextColumn::make('category')->label('Type'),
            Tables\Columns\TextColumn::make('amount')->money('eur')->label('Montant'),
            Tables\Columns\TextColumn::make('property.title')->label('Bien immobilier'),
        ])->actions([Tables\Actions\EditAction::make()]);
    }
    public static function getPages(): array { return ['index' => Pages\ListExpenses::route('/')]; }
}