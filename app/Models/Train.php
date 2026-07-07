<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Train extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'type', 'wagon_count', 'seats_per_wagon', 'wagon_classes',
    ];

    protected function casts(): array
    {
        return [
            'wagon_classes' => 'array',
        ];
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
}