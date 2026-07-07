<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wagon_id')->constrained()->cascadeOnDelete();

            $table->string('seat_number', 5); // np. "12A"
            $table->enum('position', ['window', 'aisle', 'middle'])->default('aisle');

            // Stan "twardy" w DB (finalny). Blokady 10-minutowe żyją w Redis, NIE tutaj.
            $table->enum('status', ['available', 'sold'])->default('available');

            $table->timestamps();

            $table->unique(['wagon_id', 'seat_number']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
