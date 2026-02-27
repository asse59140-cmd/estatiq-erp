<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AIAnalysisResource\Pages;
use App\Models\AIAnalysis;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Notifications\Notification;

class AIAnalysisResource extends Resource
{
    protected static ?string $model = AIAnalysis::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    
    protected static ?string $navigationLabel = 'Analyses IA';
    
    protected static ?string $navigationGroup = 'Intelligence Artificielle';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations de l\'Analyse')
                    ->schema([
                        Forms\Components\Select::make('analysis_type')
                            ->label('Type d\'analyse')
                            ->options([
                                'market_trends' => 'Tendances du Marché',
                                'tenant_behavior' => 'Comportement Locataire',
                                'property_valuation' => 'Évaluation Propriété',
                                'maintenance_prediction' => 'Prédiction Maintenance',
                                'document_analysis' => 'Analyse Document',
                                'smart_report' => 'Rapport Intelligent',
                                'chat_assistant' => 'Assistant Conversationnel',
                                'risk_assessment' => 'Évaluation des Risques',
                                'financial_forecast' => 'Prévision Financière',
                                'portfolio_optimization' => 'Optimisation Portefeuille',
                            ])
                            ->required()
                            ->disabled(fn ($record) => $record && $record->exists),
                        
                        Forms\Components\Select::make('provider')
                            ->label('Fournisseur IA')
                            ->options(function () {
                                return \App\Services\AIService::getAvailableProviders();
                            })
                            ->default('gemini')
                            ->required()
                            ->disabled(fn ($record) => $record && $record->exists),
                        
                        Forms\Components\TextInput::make('confidence_score')
                            ->label('Score de Confiance')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(1)
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('processing_time')
                            ->label('Temps de Traitement (s)')
                            ->numeric()
                            ->step(0.1)
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('cost')
                            ->label('Coût')
                            ->numeric()
                            ->step(0.000001)
                            ->prefix('€')
                            ->disabled(),
                    ])
                    ->columns(2),

                Tabs::make('Données')
                    ->tabs([
                        Tabs\Tab::make('Données d\'entrée')
                            ->schema([
                                Forms\Components\KeyValue::make('input_data')
                                    ->label('Données d\'entrée')
                                    ->columnSpanFull()
                                    ->disabled(),
                            ]),
                        
                        Tabs\Tab::make('Résultats')
                            ->schema([
                                Forms\Components\KeyValue::make('output_data')
                                    ->label('Résultats de l\'analyse')
                                    ->columnSpanFull()
                                    ->disabled(),
                            ]),
                        
                        Tabs\Tab::make('Métadonnées')
                            ->schema([
                                Forms\Components\KeyValue::make('metadata')
                                    ->label('Métadonnées')
                                    ->columnSpanFull()
                                    ->disabled(),
                            ]),
                    ]),

                Section::make('Validation')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'processing' => 'En cours',
                                'completed' => 'Terminé',
                                'failed' => 'Échoué',
                                'validated' => 'Validé',
                                'rejected' => 'Rejeté',
                            ])
                            ->default('pending')
                            ->required(),
                        
                        Forms\Components\Select::make('validated_by')
                            ->label('Validé par')
                            ->relationship('validatedBy', 'name')
                            ->disabled(),
                        
                        Forms\Components\DateTimePicker::make('validated_at')
                            ->label('Date de validation')
                            ->native(false)
                            ->disabled(),
                        
                        Forms\Components\Textarea::make('feedback')
                            ->label('Feedback')
                            ->rows(3)
                            ->helperText('Vos commentaires sur la qualité de cette analyse'),
                        
                        Forms\Components\Textarea::make('error_message')
                            ->label('Message d\'erreur')
                            ->rows(2)
                            ->visible(fn ($record) => $record && $record->status === 'failed')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('analysis_type')
                    ->label('Type d\'analyse')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'market_trends' => 'info',
                        'tenant_behavior' => 'warning',
                        'property_valuation' => 'success',
                        'maintenance_prediction' => 'danger',
                        'document_analysis' => 'primary',
                        'smart_report' => 'gray',
                        'chat_assistant' => 'purple',
                        'risk_assessment' => 'orange',
                        'financial_forecast' => 'green',
                        'portfolio_optimization' => 'blue',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => $state ? 
                        (new AIAnalysis())->getAnalysisTypeLabelAttribute() : ''),
                
                Tables\Columns\TextColumn::make('provider')
                    ->label('Fournisseur')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'gemini' => 'blue',
                        'openai' => 'green',
                        'anthropic' => 'red',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                
                Tables\Columns\TextColumn::make('confidence_score')
                    ->label('Confiance')
                    ->numeric(2)
                    ->sortable()
                    ->color(fn ($state) => match (true) {
                        $state >= 0.9 => 'success',
                        $state >= 0.7 => 'warning',
                        $state >= 0.5 => 'primary',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state ? ($state * 100) . '%' : 'N/A'),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'validated' => 'success',
                        'rejected' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => 
                        (new AIAnalysis())->getStatusLabelAttribute()),
                
                Tables\Columns\TextColumn::make('processing_time')
                    ->label('Temps')
                    ->numeric(2)
                    ->suffix('s')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('cost')
                    ->label('Coût')
                    ->money('EUR', true)
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\IconColumn::make('is_validated')
                    ->label('Validé')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                
                Tables\Columns\TextColumn::make('validatedBy.name')
                    ->label('Validé par')
                    ->toggleable()
                    ->placeholder('Non validé'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('analysis_type')
                    ->label('Type d\'analyse')
                    ->options([
                        'market_trends' => 'Tendances du Marché',
                        'tenant_behavior' => 'Comportement Locataire',
                        'property_valuation' => 'Évaluation Propriété',
                        'maintenance_prediction' => 'Prédiction Maintenance',
                        'document_analysis' => 'Analyse Document',
                        'smart_report' => 'Rapport Intelligent',
                        'chat_assistant' => 'Assistant Conversationnel',
                        'risk_assessment' => 'Évaluation des Risques',
                        'financial_forecast' => 'Prévision Financière',
                        'portfolio_optimization' => 'Optimisation Portefeuille',
                    ]),
                
                Tables\Filters\SelectFilter::make('provider')
                    ->label('Fournisseur')
                    ->options(function () {
                        return \App\Services\AIService::getAvailableProviders();
                    }),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'processing' => 'En cours',
                        'completed' => 'Terminé',
                        'failed' => 'Échoué',
                        'validated' => 'Validé',
                        'rejected' => 'Rejeté',
                    ]),
                
                Tables\Filters\Filter::make('high_confidence')
                    ->label('Haute confiance')
                    ->query(fn ($query) => $query->where('confidence_score', '>=', 0.8)),
                
                Tables\Filters\Filter::make('low_confidence')
                    ->label('Faible confiance')
                    ->query(fn ($query) => $query->where('confidence_score', '<', 0.5)),
                
                Tables\Filters\Filter::make('validated')
                    ->label('Validées')
                    ->query(fn ($query) => $query->whereNotNull('validated_at')),
                
                Tables\Filters\Filter::make('failed')
                    ->label('Échouées')
                    ->query(fn ($query) => $query->where('status', 'failed')),
                
                Tables\Filters\Filter::make('expensive')
                    ->label('Coûteuses')
                    ->query(fn ($query) => $query->where('cost', '>', 1.0)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),
                
                Tables\Actions\Action::make('validate')
                    ->label('Valider')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'completed' && !$record->is_validated)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->validateAnalysis(auth()->user(), true);
                        
                        Notification::make()
                            ->title('Analyse validée')
                            ->success()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'completed' && !$record->is_validated)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->validateAnalysis(auth()->user(), false);
                        
                        Notification::make()
                            ->title('Analyse rejetée')
                            ->warning()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('regenerate')
                    ->label('Régénérer')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn ($record) => in_array($record->status, ['failed', 'completed']))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // Logique de régénération à implémenter
                        Notification::make()
                            ->title('Régénération en cours')
                            ->info()
                            ->send();
                    }),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('validate_bulk')
                        ->label('Valider sélection')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($records) => $records->every(fn ($record) => 
                            $record->status === 'completed' && !$record->is_validated))
                        ->action(fn ($records) => 
                            $records->each->validateAnalysis(auth()->user(), true)),
                    
                    Tables\Actions\BulkAction::make('reject_bulk')
                        ->label('Rejeter sélection')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($records) => $records->every(fn ($record) => 
                            $record->status === 'completed' && !$record->is_validated))
                        ->action(fn ($records) => 
                            $records->each->validateAnalysis(auth()->user(), false)),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Relations à ajouter : AIConversationRelationManager, etc.
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAIAnalyses::route('/'),
            'create' => Pages\CreateAIAnalysis::route('/create'),
            'view' => Pages\ViewAIAnalysis::route('/{record}'),
            'edit' => Pages\EditAIAnalysis::route('/{record}/edit'),
        ];
    }
}