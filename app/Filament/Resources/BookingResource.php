<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Booking;
use App\Models\BookingApprovals;
use App\Models\Driver;
use App\Models\User;
use App\Models\Staff;
use App\Models\Vehicle;
use Faker\Provider\ar_EG\Text;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Relationship;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use League\Flysystem\Visibility;
use Livewire\Attributes\Reactive;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    static function getDriver(int $workplace)
    {
        return Driver::whereHas('staff.company', function (Builder $query) use ($workplace) {
            $query->where('company_id', $workplace);
        })->get();
    }

    static function getVehicle(String $type)
    {
        return Vehicle::where('vehicle_type', $type)
            ->where('status', 'available')
            ->get();
    }

    static function initOptions()
    {
        $users = User::where('role', 'admin')->get();
        $staff = Staff::all();
        $approver = User::where('role', 'approver')->get();

        $options = [
            'user' => [],
            'staff' => [],
            'approver' => [],
        ];

        foreach ($users as $user) {
            $options['user'][$user->id] = $user->name;
        }

        foreach ($staff as $stf) {
            $options['staff'][$stf->staff_id] = $stf->full_name;
        }

        foreach ($approver as $app) {
            $options['approver'][$app->id] = $app->name;
        }

        return $options;
    }

    public static function form(Form $form): Form
    {
        $options = static::initOptions();
        $current_user = Auth::user();

        $record = $form->getRecord();

        return $form
            ->schema(
                array_merge(
                    [
                        Forms\Components\Select::make('admin_id')
                            // ->hidden()
                            ->options([$current_user->id => $current_user->name])
                            ->default($current_user->id)
                            ->disableOptionWhen(fn(): bool => true)
                            ->selectablePlaceholder(false)
                            ->afterStateHydrated(fn($set) => $set('admin_id', $current_user->id)),

                        Forms\Components\Select::make('booker_id')
                            ->label('Booker')
                            ->options($options['staff'])
                            ->required(),

                        Forms\Components\Select::make('vehicle_type')
                            ->label('Vehicle Type')
                            ->options([
                                'goods' => 'Goods',
                                'person' => 'Escort',
                            ])
                            ->afterStateHydrated(
                                fn($set) =>
                                $record &&
                                    $set(
                                        'vehicle_type',
                                        Vehicle::find($record->vehicle_id)->vehicle_type ?? null
                                    )
                            )
                            ->reactive()
                            ->required(),

                        Forms\Components\Select::make('vehicle_id')
                            ->label('Vehicle')
                            ->preload()
                            ->options(function ($get) {
                                $vehicle_type = $get('vehicle_type');

                                if (!$vehicle_type) {
                                    return [];
                                }

                                $data = static::getVehicle($vehicle_type);
                                $vehicleOps = [];

                                foreach ($data as $vehicle) {
                                    $vehicleOps[$vehicle->vehicle_id] = ($vehicle->brand . ' ' . $vehicle->model . ' [' . $vehicle->license_plate . ']');
                                }
                                return $vehicleOps;
                            })
                            ->required(),

                        Forms\Components\Select::make('driver_id')
                            ->label('Driver')
                            ->preload()
                            ->options(function ($get) {
                                $admin_id = $get('admin_id');

                                if (!$admin_id) {
                                    return [];
                                }

                                $data = static::getDriver($admin_id);
                                $driverOps = [];

                                foreach ($data as $driver) {
                                    $driverOps[$driver->driver_id] = $driver->staff->full_name;
                                }
                                return $driverOps;
                            })
                            ->required()
                            ->afterStateHydrated(
                                // dd($record)
                                fn($set) => $set('driver_id', $record->driver_id ?? null)
                            ),

                        Forms\Components\DatePicker::make('booking_date')->label('Booking Date')
                            ->readOnly()
                            ->default(now()->format('Y-m-d'))
                            ->hidden(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),

                        Forms\Components\DatePicker::make('start_date')->label('Start Date')
                            ->format('Y-m-d')
                            ->required(),

                        Forms\Components\DatePicker::make('end_date')->label('End Date')
                            ->format('Y-m-d')
                            ->required(),

                        Forms\Components\Textarea::make('purpose')
                            ->label('Purpose')
                            ->afterStateHydrated(fn($set) => $set('purpose', $record->purpose ?? null)),

                        Repeater::make('booking_approvals')
                            ->schema([
                                Forms\Components\Select::make('approver_id')
                                    ->label('Approver')
                                    ->multiple()
                                    ->preload()
                                    ->options(function () {
                                        $approver = User::where('role', 'approver')
                                            ->where('company_id', Auth::user()->company_id)
                                            ->get();
                                        return $approver->pluck('name', 'id');
                                    })
                                    ->required(),
                            ])
                            ->visibleOn('create')
                            ->minItems(1),
                    ],
                    $record ? array_map(
                        fn($approval) =>
                        // error_log(json_encode($approval))
                        Forms\Components\Select::make('approver-' . $approval['approval_level'])
                            ->label('Approver Level ' . $approval['approval_level'])
                            ->multiple()
                            ->preload()
                            ->options(function () use ($record) {
                                $bookingApprove = BookingApprovals::where('booking_id', $record->booking_id)
                                    ->get()
                                    ->toArray();
                                $data = [];
                                foreach ($bookingApprove as $booking) {
                                    $data[] = [$booking['approver_id'] => User::find($booking['approver_id'])->name];
                                }
                                return $data;
                            })
                            ->afterStateHydrated(
                                function ($set) use ($approval, $record) {
                                    $approver = BookingApprovals::select(['approval_level', 'booking_id', 'approver_id'])
                                        ->where('booking_id', $record->booking_id)
                                        ->where('approval_level', $approval['approval_level'])
                                        ->get()
                                        ->toArray();

                                    $data = [];
                                    foreach ($approver as $app) {
                                        $data[] = $app['approver_id'];
                                    }
                                    $set('approver-' . $approval['approval_level'], $data);
                                }
                            )
                        //
                        ,
                        BookingApprovals::select(['approval_level', 'booking_id'])
                            ->distinct()
                            ->where('booking_id', $record->booking_id)
                            ->get()
                            ->toArray(),
                    ) : []
                )
            );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Admin')->searchable(),
                TextColumn::make('staff.full_name')->label('Booker')->searchable(),
                TextColumn::make('driver.staff.full_name')->label('Driver')->searchable(),
                TextColumn::make('vehicle.vehicle_type')->label('Vehicle Type'),
                TextColumn::make('booking_date')->label('Booking Date'),
                TextColumn::make('status')->label('Status'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
        ];
    }
}
