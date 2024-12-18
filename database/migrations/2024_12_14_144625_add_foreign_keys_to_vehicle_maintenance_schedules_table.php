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
        Schema::table('vehicle_maintenance_schedules', function (Blueprint $table) {
            $table->foreign(['vehicle_id'], 'vehicle_maintenance_schedules_ibfk_1')->references(['vehicle_id'])->on('vehicles')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_maintenance_scedules', function (Blueprint $table) {
            $table->dropForeign('vehicle_maintenance_schedules_ibfk_1');
        });
    }
};
