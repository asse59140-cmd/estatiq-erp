<?php
namespace App\Filament\Resources;
use App\Filament\Resources\MaintenanceRequestResource\Pages;
use App\Models\MaintenanceRequest;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class MaintenanceRequestResource extends Resource {
    protected static ?string $model = MaintenanceRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Maintenance';

    public static function form(Forms\Form $form): Forms\Form {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('Problème')->required(),
            Forms\Components\Select::make('property_id')->relationship('property', 'title')->label('Propriété')->required(),
            Forms\Components\Select::make('priority')->options(['low'=>'Basse','medium'=>'Moyenne','high'=>'Haute','emergency'=>'URGENT'])->required(),
            Forms\Components\Select::make('status')->options(['pending'=>'En attente','in_progress'=>'En cours','completed'=>'Terminé'])->default('pending'),
            Forms\Components\Textarea::make('description')->columnSpanFull(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->label('Sujet')->searchable(),
            Tables\Columns\TextColumn::make('property.title')->label('Bien'),
            Tables\Columns\TextColumn::make('priority')->badge()->color(fn($state)=>$state=='emergency'?'danger':'warning'),
            Tables\Columns\SelectColumn::make('status')->options(['pending'=>'En attente','in_progress'=>'En cours','completed'=>'Terminé']),
        ])->actions([Tables\Actions\EditAction::make()]);
    }
    public static function getPages(): array { return ['index' => Pages\ListMaintenanceRequests::route('/')]; }
}