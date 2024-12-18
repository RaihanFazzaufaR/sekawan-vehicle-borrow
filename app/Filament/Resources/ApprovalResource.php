<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApprovalResource\Pages;
use App\Filament\Resources\ApprovalResource\RelationManagers;
use App\Models\BookingApprovals;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalResource extends Resource
{
    protected static ?string $model = BookingApprovals::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $record = $form->getRecord();

        return $form
            ->schema([
                Forms\Components\TextInput::make('booking.user.name')
                    ->label('Admin')
                    ->afterStateHydrated(function ($set) use ($record) {
                        $set('booking.user.name', $record->booking->user->name);
                    })->disabled(),
                Forms\Components\TextInput::make('booking.staff.full_name')
                    ->label('Booker')
                    ->afterStateHydrated(function ($set) use ($record) {
                        $set('booking.staff.full_name', $record->booking->staff->full_name);
                    })->disabled(),
                Forms\Components\TextInput::make('booking.driver.staff.full_name')
                    ->label('Driver')
                    ->afterStateHydrated(function ($set) use ($record) {
                        $set('booking.driver.staff.full_name', $record->booking->driver->staff->full_name);
                    })->disabled(),
                Forms\Components\TextInput::make('booking.vehicle.license_plate')
                    ->label('Vehicle')
                    ->afterStateHydrated(function ($set) use ($record) {
                        $set('booking.vehicle.license_plate', $record->booking->vehicle->license_plate);
                    })->disabled(),
                Forms\Components\TextInput::make('booking.vehicle.brand')
                    ->label('Brand')
                    ->afterStateHydrated(function ($set) use ($record) {
                        $set('booking.vehicle.brand', $record->booking->vehicle->brand);
                    })->disabled(),
                Forms\Components\TextInput::make('booking.vehicle.model')
                    ->label('Model')
                    ->afterStateHydrated(function ($set) use ($record) {
                        $set('booking.vehicle.model', $record->booking->vehicle->model);
                    })->disabled(),
                Forms\Components\DatePicker::make('booking.booking_date')
                    ->label('Booking Date')
                    ->afterStateHydrated(function ($set) use ($record) {
                        $set('booking.booking_date', $record->booking->booking_date);
                    })->disabled()
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('booking.start_date')
                    ->label('Start Date')
                    ->afterStateHydrated(function ($set) use ($record) {
                        $set('booking.start_date', $record->booking->start_date);
                    })->disabled(),
                Forms\Components\DatePicker::make('booking.end_date')
                    ->label('End Date')
                    ->afterStateHydrated(function ($set) use ($record) {
                        $set('booking.end_date', $record->booking->end_date);
                    })->disabled(),
                Forms\Components\TextInput::make('approval_level')
                    ->label('Approval Level')
                    ->afterStateHydrated(function ($set) use ($record) {
                        $set('approval_level', $record->approval_level);
                    })->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->disabled(fn ($get) => in_array($get('status'), ['approved', 'rejected'])),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('bookings.booking_date', 'desc')
            ->columns([
                TextColumn::make('booking.user.name')->label('Admin'),
                TextColumn::make('booking.staff.full_name')->label('Booker'),
                TextColumn::make('booking.driver.staff.full_name')->label('Driver'),
                TextColumn::make('booking.vehicle.license_plate')->label('Vehicle'),
                (Auth::user()->role == 'admin' ?
                    TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'rejected' => 'danger',
                        'approved' => 'success',
                        'pending' => 'info',
                    }) :
                    SelectColumn::make('status')->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->rules(['required'])
                    ->label('Status')
                    ->disabled(fn ($record) => in_array($record->status, ['approved', 'rejected']))),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();
                $query->select('booking_approvals.*') // Ensure the primary key is included in the select statement
                      ->join('bookings', 'booking_approvals.booking_id', '=', 'bookings.booking_id');
                if ($user->role !== 'admin') {
                    $query->where('approver_id', $user->id)
                        ->where(function ($query) {
                            $query->where('approval_level', 1)
                                ->orWhere(function ($query) {
                                    $query->where('approval_level', '>', 1)
                                        ->whereNotExists(function ($query) {
                                            $query->select(DB::raw(1))
                                                ->from('booking_approvals as ba')
                                                ->whereColumn('ba.booking_id', 'booking_approvals.booking_id')
                                                ->where('ba.approval_level', '<', DB::raw('booking_approvals.approval_level'))
                                                ->where('ba.status', '!=', 'approved');
                                        });
                                });
                        });
                }
            })
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListApprovals::route('/'),
            'edit' => Pages\EditApproval::route('/{record}/edit'),
        ];
    }
}