<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'pnr_code', 'trip_id', 'seat_id', 'user_id',
        'guest_name', 'guest_email', 'price',
        'payment_status', 'payment_reference', 'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            $ticket->pnr_code ??= static::generateUniquePnr();
        });
    }

    public static function generateUniquePnr(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('pnr_code', $code)->exists());

        return $code;
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function passengerName(): string
    {
        return $this->user?->name ?? $this->guest_name ?? 'Gość';
    }
}