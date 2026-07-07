<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trains', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // np. "Pendolino ED250"
            $table->string('code')->unique();        // np. "PKP-IC-101"
            $table->enum('type', ['express', 'intercity', 'regional']);
            $table->unsignedTinyInteger('wagon_count');
            $table->unsignedTinyInteger('seats_per_wagon');
            $table->json('wagon_classes')->nullable(); // np. {"1": "first", "2": "second", ...}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trains');
    }
};
