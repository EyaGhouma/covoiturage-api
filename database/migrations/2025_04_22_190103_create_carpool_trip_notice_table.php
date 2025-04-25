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
        Schema::create('carpool_trip_notices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('passenger_id');
            $table->unsignedBigInteger('car_pool_trip_id');
            $table->float('rate');
            $table->string('comment');
            $table->timestamps();
            $table->unique(['passenger_id', 'car_pool_trip_id']);
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('passenger_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('car_pool_trip_id')->references('id')->on('carpool_trips')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carpool_trip_notices');
    }
};
