<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->string('pnr_code', 8)->unique(); // kod rezerwacji, np. "X7K9P2M1"

            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seat_id')->constrained()->cascadeOnDelete();

            // Gość albo zalogowany
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();

            $table->decimal('price', 8, 2);

            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])
                  ->default('pending');
            $table->string('payment_reference')->nullable(); // mock Stripe payment_intent id

            $table->enum('status', ['reserved', 'confirmed', 'cancelled'])
                  ->default('reserved');

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('guest_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
