<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'train_id',
        'departure_city_id',
        'arrival_city_id',
        'origin_station',
        'destination_station',
        'departure_at',
        'arrival_at',
        'base_price',
        'status',
        'auto_generated',
    ];

    protected function casts(): array
    {
        return [
            'departure_at' => 'datetime',
            'arrival_at' => 'datetime',
            'auto_generated' => 'boolean',
            'base_price' => 'decimal:2',
        ];
    }

    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }

    public function departureCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'departure_city_id');
    }

    public function arrivalCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'arrival_city_id');
    }

    public function wagons(): HasMany
    {
        return $this->hasMany(Wagon::class);
    }

    public function seats(): HasManyThrough
    {
        return $this->hasManyThrough(Seat::class, Wagon::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    // Czy pociąg już odjechał wg zegara systemowego?
    public function hasDeparted(): bool
    {
        return $this->departure_at->isPast();
    }

    /**
     * Kursy dostępne jeszcze do zakupu.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->where('departure_at', '>', now())
            ->where('status', 'scheduled')
            ->orderBy('departure_at');
    }

    /**
     * Kursy widoczne w wersji demo: przyszłe oraz odjechane maksymalnie godzinę temu.
     */
    public function scopeVisibleInDemo(Builder $query): Builder
    {
        return $query
            ->where('departure_at', '>', now()->subHour())
            ->where('status', '!=', 'cancelled')
            ->orderBy('departure_at');
    }
}
