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
        Schema::create('vehicle_maintenance_schedules', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->date('maintenance_date');
            $table->text('description')->nullable();
            $table->enum('status', [ 'scheduled', 'on-progress', 'completed'])->default('scheduled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance_schedules');
    }
};
