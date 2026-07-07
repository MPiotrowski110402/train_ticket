<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wagons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('number'); // numer wagonu w danym kursie: 1, 2, 3...
            $table->enum('class', ['first', 'second']);
            $table->unsignedTinyInteger('rows')->default(10);
            $table->unsignedTinyInteger('seats_per_row')->default(4);

            $table->timestamps();

            $table->unique(['trip_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wagons');
    }
};
