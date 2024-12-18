<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign(['admin_id'], 'bookings_ibfk_1')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['vehicle_id'], 'bookings_ibfk_2')->references(['vehicle_id'])->on('vehicles')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['driver_id'], 'bookings_ibfk_3')->references(['driver_id'])->on('drivers')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['booker_id'], 'bookings_ibfk_4')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign('bookings_ibfk_1');
            $table->dropForeign('bookings_ibfk_2');
            $table->dropForeign('bookings_ibfk_3');
            $table->dropForeign('bookings_ibfk_4');
        });
    }
};
