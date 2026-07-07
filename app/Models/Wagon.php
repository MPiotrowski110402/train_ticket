<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wagon extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id', 'number', 'class', 'rows', 'seats_per_row',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }
}