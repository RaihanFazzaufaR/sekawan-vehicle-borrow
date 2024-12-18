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
        Schema::table('vehicle_logs', function (Blueprint $table) {
            $table->foreign(['vehicle_id'], 'vehicle_logs_ibfk_1')->references(['vehicle_id'])->on('vehicles')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_logs', function (Blueprint $table) {
            $table->dropForeign('vehicle_logs_ibfk_1');
            $table->dropForeign('vehicle_logs_ibfk_2');
        });
    }
};
