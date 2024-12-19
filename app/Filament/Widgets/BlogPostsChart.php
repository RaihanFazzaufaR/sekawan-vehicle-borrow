<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        $startDate = now()->subDays(13);
        $endDate = now();

        $bookings = Booking::select(DB::raw('DATE(booking_date) as date'), DB::raw('count(*) as count'))
        ->whereBetween('booking_date', [$startDate, $endDate])
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->keyBy('date');

        $dates = [];
        $counts = [];

        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            $formattedDate = $date->format('M d');
            $dates[] = $formattedDate;
            $counts[] = $bookings->get($date->format('Y-m-d'))->count ?? 0;
        }

        return [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Bookings',
                    'data' => $counts,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    function getColumnSpan(): array|int|string
    {
        return 'full';
    }
}
