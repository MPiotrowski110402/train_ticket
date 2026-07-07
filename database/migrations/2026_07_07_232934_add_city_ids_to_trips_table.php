<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->foreignId('departure_city_id')
                ->nullable()
                ->constrained('cities')
                ->nullOnDelete();

            $table->foreignId('arrival_city_id')
                ->nullable()
                ->constrained('cities')
                ->nullOnDelete();

            $table->index([
                'departure_city_id',
                'arrival_city_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropIndex([
                'departure_city_id',
                'arrival_city_id',
            ]);

            $table->dropConstrainedForeignId('departure_city_id');
            $table->dropConstrainedForeignId('arrival_city_id');
        });
    }
};