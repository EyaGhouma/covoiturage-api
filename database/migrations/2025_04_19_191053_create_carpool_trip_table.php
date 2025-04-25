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
        Schema::create('carpool_trips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('departure');
            $table->string('arrival');
            $table->dateTime('date');
            $table->float('duration');
            $table->integer('availableSeats');
            $table->integer('totalSeats');
            $table->string('luggageType');
            $table->boolean('petsAllowed');
            $table->boolean('smokingAllowed');
            $table->float('price');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carpool_trips');
    }
};
