<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers\VehicleLogsRelationManager;
use App\Filament\Resources\VehicleResource\RelationManagers\VehicleMaintenanceScheduleRelationManager;
use App\Models\Vehicle;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-stop-circle';

    public static function form(Form $form): Form
    {
        $record = $form->getRecord();

        return $form
            ->schema([
                Forms\Components\TextInput::make('license_plate')
                    ->label('License Plate')
                    ->required()
                    ->placeholder('Enter the license plate of the vehicle'),
                Forms\Components\Select::make('vehicle_type')
                    ->label('Vehicle Type')
                    ->required()
                    ->options([
                        'goods' => 'Goods',
                        'person' => 'Person',
                    ]),
                Forms\Components\TextInput::make('brand')
                    ->label('Brand')
                    ->required()
                    ->placeholder('Enter the brand of the vehicle'),
                Forms\Components\TextInput::make('model')
                    ->label('Model')
                    ->required()
                    ->placeholder('Enter the model of the vehicle'),
                Forms\Components\Select::make('ownership_id')
                    ->label('Ownership')
                    ->options(function () {
                        $rentalCompanies = Company::whereIn('status', ['rental', 'central'])->pluck('name', 'company_id')->toArray();
                        return $rentalCompanies;
                    })
                    ->afterStateHydrated(function ($set) use ($record) {
                        $set('ownership_id', $record->ownership_id ?? null);
                    }),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Available',
                        'unavailable' => 'Unavailable',
                    ])
                    ->afterStateHydrated(function ($set) use ($record) {
                        $set('status', $record->status ?? 'available');
                    })
                    ->visibleOn(['view', 'edit']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vehicle_type')
                    ->searchable(),
                TextColumn::make('brand')
                    ->searchable(),
                TextColumn::make('model')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'unavailable' => 'Unavailable',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            VehicleLogsRelationManager::class,
            VehicleMaintenanceScheduleRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
