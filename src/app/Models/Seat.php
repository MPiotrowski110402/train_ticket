<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;

class Seat extends Model
{
    use HasFactory;

    protected $fillable = [
        'wagon_id', 'seat_number', 'position', 'status',
    ];

    public function wagon(): BelongsTo
    {
        return $this->belongsTo(Wagon::class);
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class);
    }

    /**
     * Klucz cache'a dla tymczasowej blokady w Redis.
     */
    public function lockKey(): string
    {
        return "seat_lock:{$this->id}";
    }

    /**
     * Czy miejsce jest tymczasowo zablokowane przez kogoś innego (Redis TTL).
     */
    public function isTemporarilyLocked(): bool
    {
        return Cache::has($this->lockKey());
    }

    /**
     * Pełny stan "z perspektywy frontu": available / locked / sold.
     */
    public function effectiveStatus(): string
    {
        if ($this->status === 'sold') {
            return 'sold';
        }

        return $this->isTemporarilyLocked() ? 'locked' : 'available';
    }
}