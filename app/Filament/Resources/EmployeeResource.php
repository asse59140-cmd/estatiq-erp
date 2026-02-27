<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Employés';
    
    protected static ?string $navigationGroup = 'Ressources Humaines';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Informations Employé')
                    ->tabs([
                        Tab::make('Informations Personnelles')
                            ->schema([
                                Section::make('Identité')
                                    ->schema([
                                        Forms\Components\TextInput::make('employee_number')
                                            ->label('Matricule')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->placeholder('EMP001'),
                                        
                                        Forms\Components\TextInput::make('first_name')
                                            ->label('Prénom')
                                            ->required()
                                            ->maxLength(255),
                                        
                                        Forms\Components\TextInput::make('last_name')
                                            ->label('Nom')
                                            ->required()
                                            ->maxLength(255),
                                        
                                        Forms\Components\DatePicker::make('date_of_birth')
                                            ->label('Date de naissance')
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->maxDate(now()->subYears(18)),
                                        
                                        Forms\Components\Select::make('gender')
                                            ->label('Genre')
                                            ->options([
                                                'male' => 'Masculin',
                                                'female' => 'Féminin',
                                                'other' => 'Autre',
                                            ])
                                            ->nullable(),
                                        
                                        Forms\Components\Select::make('marital_status')
                                            ->label('État civil')
                                            ->options([
                                                'single' => 'Célibataire',
                                                'married' => 'Marié(e)',
                                                'divorced' => 'Divorcé(e)',
                                                'widowed' => 'Veuf/Veuve',
                                            ])
                                            ->nullable(),
                                        
                                        Forms\Components\TextInput::make('nationality')
                                            ->label('Nationalité')
                                            ->placeholder('Française'),
                                        
                                        Forms\Components\TextInput::make('number_of_children')
                                            ->label('Nombre d\'enfants')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0),
                                    ])
                                    ->columns(2),

                                Section::make('Contact')
                                    ->schema([
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                        
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Téléphone')
                                            ->tel()
                                            ->placeholder('+33 6 12 34 56 78'),
                                        
                                        Forms\Components\Textarea::make('address')
                                            ->label('Adresse')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        
                                        Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('city')
                                                    ->label('Ville'),
                                                
                                                Forms\Components\TextInput::make('postal_code')
                                                    ->label('Code postal')
                                                    ->maxLength(10),
                                                
                                                Forms\Components\TextInput::make('country')
                                                    ->label('Pays')
                                                    ->default('France'),
                                            ]),
                                    ]),

                                Section::make('Personnes à contacter en cas d\'urgence')
                                    ->schema([
                                        Forms\Components\TextInput::make('emergency_contact')
                                            ->label('Contact d\'urgence')
                                            ->placeholder('Nom du contact'),
                                        
                                        Forms\Components\TextInput::make('emergency_phone')
                                            ->label('Téléphone d\'urgence')
                                            ->tel()
                                            ->placeholder('+33 6 12 34 56 78'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Informations Professionnelles')
                            ->schema([
                                Section::make('Poste')
                                    ->schema([
                                        Forms\Components\DatePicker::make('hire_date')
                                            ->label('Date d\'embauche')
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->required()
                                            ->maxDate(now()),
                                        
                                        Forms\Components\DatePicker::make('termination_date')
                                            ->label('Date de fin de contrat')
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->afterOrEqual('hire_date')
                                            ->nullable(),
                                        
                                        Forms\Components\Select::make('department')
                                            ->label('Département')
                                            ->options([
                                                'sales' => 'Commercial',
                                                'management' => 'Gestion',
                                                'maintenance' => 'Maintenance',
                                                'accounting' => 'Comptabilité',
                                                'administration' => 'Administration',
                                                'hr' => 'Ressources Humaines',
                                                'it' => 'Informatique',
                                                'marketing' => 'Marketing',
                                            ])
                                            ->nullable(),
                                        
                                        Forms\Components\TextInput::make('job_title')
                                            ->label('Intitulé du poste')
                                            ->placeholder('Agent Commercial'),
                                        
                                        Forms\Components\TextInput::make('position')
                                            ->label('Poste/Niveau')
                                            ->placeholder('Senior'),
                                    ])
                                    ->columns(2),

                                Section::make('Hiérarchie')
                                    ->schema([
                                        Forms\Components\Select::make('supervisor_id')
                                            ->label('Manager direct')
                                            ->relationship('supervisor', 'first_name', fn($query) => 
                                                $query->where('agency_id', auth()->user()->agencies()->first()->id)
                                                      ->where('status', 'active')
                                            )
                                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                                            ->searchable()
                                            ->preload()
                                            ->nullable(),
                                    ]),

                                Section::make('Statut')
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->label('Statut')
                                            ->options([
                                                'active' => 'Actif',
                                                'inactive' => 'Inactif',
                                                'on_leave' => 'En congé',
                                                'terminated' => 'Terminé',
                                            ])
                                            ->default('active')
                                            ->required(),
                                    ]),
                            ]),

                        Tab::make('Rémunération')
                            ->schema([
                                Section::make('Salaire')
                                    ->schema([
                                        Forms\Components\TextInput::make('salary')
                                            ->label('Salaire')
                                            ->numeric()
                                            ->prefix('€')
                                            ->required(),
                                        
                                        Forms\Components\Select::make('salary_type')
                                            ->label('Type de salaire')
                                            ->options([
                                                'monthly' => 'Mensuel',
                                                'hourly' => 'Horaire',
                                                'annual' => 'Annuel',
                                            ])
                                            ->default('monthly')
                                            ->required(),
                                        
                                        Forms\Components\TextInput::make('commission_rate')
                                            ->label('Taux de commission (%)')
                                            ->numeric()
                                            ->suffix('%')
                                            ->default(0)
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->step(0.01),
                                    ])
                                    ->columns(3),

                                Section::make('Informations bancaires')
                                    ->schema([
                                        Forms\Components\TextInput::make('bank_name')
                                            ->label('Nom de la banque')
                                            ->placeholder('BNP Paribas'),
                                        
                                        Forms\Components\TextInput::make('bank_account')
                                            ->label('Numéro de compte')
                                            ->placeholder('FR76 1234 5678 9012 3456 7890 123'),
                                        
                                        Forms\Components\TextInput::make('rib')
                                            ->label('RIB')
                                            ->placeholder('12345 67890 12345678901 23'),
                                    ])
                                    ->columns(3),

                                Section::make('Sécurité sociale')
                                    ->schema([
                                        Forms\Components\TextInput::make('social_security_number')
                                            ->label('Numéro de sécurité sociale')
                                            ->placeholder('1 23 04 69 123 456 78')
                                            ->maxLength(21),
                                    ]),
                            ]),

                        Tab::make('Documents')
                            ->schema([
                                Section::make('Photo de profil')
                                    ->schema([
                                        Forms\Components\FileUpload::make('profile_image')
                                            ->label('Photo')
                                            ->directory('employees/profiles')
                                            ->image()
                                            ->maxSize(2048) // 2MB
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Notes')
                                    ->schema([
                                        Forms\Components\RichEditor::make('notes')
                                            ->label('Notes et observations')
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'bulletList',
                                                'orderedList',
                                            ])
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_number')
                    ->label('Matricule')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nom complet')
                    ->getStateUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['last_name', 'first_name']),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->icon('heroicon-m-envelope'),
                
                Tables\Columns\TextColumn::make('department')
                    ->label('Département')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sales' => 'success',
                        'management' => 'primary',
                        'maintenance' => 'warning',
                        'accounting' => 'info',
                        'administration' => 'gray',
                        'hr' => 'danger',
                        'it' => 'purple',
                        'marketing' => 'pink',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sales' => 'Commercial',
                        'management' => 'Gestion',
                        'maintenance' => 'Maintenance',
                        'accounting' => 'Comptabilité',
                        'administration' => 'Administration',
                        'hr' => 'RH',
                        'it' => 'IT',
                        'marketing' => 'Marketing',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('job_title')
                    ->label('Poste')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('salary')
                    ->label('Salaire')
                    ->money('EUR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('Commission')
                    ->suffix('%')
                    ->numeric(2),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'on_leave' => 'warning',
                        'terminated' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        'on_leave' => 'En congé',
                        'terminated' => 'Terminé',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('supervisor.full_name')
                    ->label('Manager')
                    ->placeholder('Aucun'),
                
                Tables\Columns\TextColumn::make('hire_date')
                    ->label('Date d\'embauche')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
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
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        'on_leave' => 'En congé',
                        'terminated' => 'Terminé',
                    ]),
                
                Tables\Filters\SelectFilter::make('department')
                    ->label('Département')
                    ->options([
                        'sales' => 'Commercial',
                        'management' => 'Gestion',
                        'maintenance' => 'Maintenance',
                        'accounting' => 'Comptabilité',
                        'administration' => 'Administration',
                        'hr' => 'RH',
                        'it' => 'IT',
                        'marketing' => 'Marketing',
                    ]),
                
                Tables\Filters\Filter::make('has_commission')
                    ->label('Avec commission')
                    ->query(fn ($query) => $query->where('commission_rate', '>', 0)),
                
                Tables\Filters\Filter::make('recently_hired')
                    ->label('Récemment embauchés')
                    ->query(fn ($query) => $query->where('hire_date', '>=', now()->subMonths(3))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('attendance')
                    ->label('Présence')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->url(fn ($record) => route('filament.admin.resources.employees.attendance', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Modifier le statut')
                        ->icon('heroicon-o-pencil')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Nouveau statut')
                                ->options([
                                    'active' => 'Actif',
                                    'inactive' => 'Inactif',
                                    'on_leave' => 'En congé',
                                    'terminated' => 'Terminé',
                                ])
                                ->required(),
                        ])
                        ->action(fn ($records, $data) => $records->each->update(['status' => $data['status']])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Relations à ajouter plus tard : AttendanceRelationManager, CommissionRelationManager, etc.
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}