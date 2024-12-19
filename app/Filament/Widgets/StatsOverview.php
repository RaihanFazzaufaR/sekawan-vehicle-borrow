<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total bookings', Booking::count()),
            Stat::make('Approved booking', Booking::where('status', 'approved')->count()),
            Stat::make('Available vehicles', Vehicle::where('status', 'available')->count()),
        ];
    }
}
