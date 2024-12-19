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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Relationship;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use League\Flysystem\Visibility;
use Livewire\Attributes\Reactive;
use pxlrbt\FilamentExcel\Actions\Concerns\ExportableAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;


class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    public static function canViewAny(): bool
    {
        return Auth::user()?->role === 'admin';
    }

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
                            ->options(function () use ($current_user) {
                                return Staff::where('company_id', $current_user->company_id)->pluck('full_name', 'staff_id');
                            }),

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
                            ->required()
                            ->visibleOn('create'),

                        Forms\Components\Select::make('vehicle_id_')
                            ->label('Vehicle')
                            ->preload()
                            ->options(function ($get) use ($record) {
                                $vehicle = Vehicle::find($record->vehicle_id);
                                return [$vehicle->vehicle_id => ($vehicle->brand . ' ' . $vehicle->model . ' [' . $vehicle->license_plate . ']')];
                            })
                            ->afterStateHydrated(
                                fn($set) => $set('vehicle_id_', $record->vehicle_id ?? null)
                            )
                            ->visibleOn('view'),

                        Forms\Components\Select::make('driver_id')
                            ->label('Driver')
                            ->columnSpanFull()
                            ->preload()
                            ->options(function ($get) use ($current_user) {
                                $admin_id = $get('admin_id');

                                if (!$admin_id) {
                                    return [];
                                }

                                $data = static::getDriver($admin_id);
                                $driverOps = [];

                                foreach ($data as $driver) {
                                    if ($driver->staff->company_id == $current_user->company_id) {
                                        $driverOps[$driver->driver_id] = $driver->staff->full_name;
                                    }
                                }
                                return $driverOps;
                            })
                            ->required()
                            ->afterStateHydrated(
                                fn($set) => $set('driver_id', $record->driver_id ?? null)
                            )
                            ->visibleOn('create'),

                        Forms\Components\Select::make('driver_id_')
                            ->label('Driver')
                            ->columnSpanFull()
                            ->preload()
                            ->options(function ($get) use ($record) {
                                $driver = Driver::find($record->driver_id);
                                return [$driver->driver_id => $driver->staff->full_name];
                            })
                            ->afterStateHydrated(
                                fn($set) => $set('driver_id_', $record->driver_id ?? null)
                            )
                            ->visibleOn('view'),

                        Forms\Components\DatePicker::make('booking_date')->label('Booking Date')
                            ->readOnly()
                            ->columnSpanFull()
                            ->default(now()->format('Y-m-d'))
                            ->visibleOn('view'),

                        Forms\Components\DatePicker::make('start_date')->label('Start Date')
                            ->format('Y-m-d')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\DatePicker::make('end_date')->label('End Date')
                            ->format('Y-m-d')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('purpose')
                            ->label('Purpose')
                            ->columnSpanFull()
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
                            ->columnSpanFull()
                            ->minItems(1),
                    ],
                    $record ? array_map(
                        fn($approval) =>
                        // error_log(json_encode($approval))
                        Forms\Components\Select::make('approver-' . $approval['approval_level'])
                            ->label('Approver Level ' . $approval['approval_level'])
                            ->columnSpanFull()
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
                            ),
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
            ->defaultSort('booking_date', '')
            ->columns([
                TextColumn::make('user.name')->label('Admin')->searchable(),
                TextColumn::make('staff.full_name')->label('Booker')->searchable(),
                TextColumn::make('driver.staff.full_name')->label('Driver')->searchable(),
                TextColumn::make('vehicle.vehicle_type')->label('Vehicle Type'),
                TextColumn::make('booking_date')->label('Booking Date'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'rejected' => 'danger',
                        'approved' => 'success',
                        'pending' => 'info',
                    }),
            ])
            ->modifyQueryUsing(function (Builder $query) {})
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Filter::make('booking_date')
                    ->form([
                        DatePicker::make('from')
                            ->label('Start Date')
                            ->required()
                            ->columnSpan(6),
                        DatePicker::make('until')
                            ->label('End Date')
                            ->required()
                            ->columnSpan(6),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('booking_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('booking_date', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                ExportAction::make()->exports([
                    ExcelExport::make()
                    ->modifyQueryUsing(function (Builder $query) {

                        $filters = request()->input('components.0.snapshot');

                        if ($filters) {
                            $decodedFilters = json_decode($filters, true);
                            $tableFilters = $decodedFilters['data']['tableFilters'] ?? [];
                            $statusFilter = $tableFilters[0]['status'][0]; 
                            $dateFilters = $tableFilters[0]['booking_date'][0];

                            if ($dateFilters['from']) {
                                $query->whereDate('booking_date', '>=', $dateFilters['from']);
                            }
                            
                            if($dateFilters['until']) {
                                $query->whereDate('booking_date', '<=', $dateFilters['until']);
                            }

                            if ($statusFilter['value']) {
                                return $query->where('status', $statusFilter['value']);
                            }
                        }
                        return $query;
                    })
                    ->withColumns([
                        Column::make('user.name')->heading('Admin'),
                        Column::make('staff.full_name')->heading('Booker'),
                        Column::make('vehicle.vehicle_type')->heading('Vehicle Type'),
                        Column::make('vehicle.brand')->heading('Brand'),
                        Column::make('vehicle.model')->heading('Model'),
                        Column::make('vehicle.license_plate')->heading('License Plate'),
                        Column::make('driver.staff.full_name')->heading('Driver'),
                        Column::make('booking_date')->heading('Booking Date'),
                        Column::make('start_date')->heading('Start Date'),
                        Column::make('end_date')->heading('End Date'),
                        Column::make('purpose')->heading('Purpose'),
                        Column::make('status')->heading('Status'),
                    ])
                ]),
            ])
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
