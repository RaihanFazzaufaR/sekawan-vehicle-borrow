<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\BookingApprovals;
use App\Models\Driver;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use SebastianBergmann\Type\VoidType;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function afterCreate()
    {
        $formData = $this->form->getState();
        $record = $this->record;

        // dd($record);

        for ($i = 0; $i < count($formData['booking_approvals']); $i++) {
            $approvals = $formData['booking_approvals'][$i];
            foreach ($approvals['approver_id'] as $approval) {
                BookingApprovals::create([
                    'booking_id' => $record['booking_id'],
                    'approver_id' => $approval,
                    'approval_level' => $i + 1,
                    'status' => 'pending',
                    'approved_at' => null,
                ]);
            }
        }

        Driver::where('driver_id', $formData['driver_id'])->update(['status' => 'unavailable']);

        Vehicle::where('vehicle_id', $formData['vehicle_id'])->update(['status' => 'booked']);

        return redirect('/admin/bookings');
    }
}
