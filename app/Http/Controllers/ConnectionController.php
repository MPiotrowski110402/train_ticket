<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Trip;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ConnectionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'integer', 'exists:cities,id'],
            'to' => ['nullable', 'integer', 'exists:cities,id', 'different:from'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $cities = City::active()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $trips = Trip::query()
            ->visibleInDemo()
            ->with([
                'train:id,name,code,type',
                'departureCity:id,name,slug',
                'arrivalCity:id,name,slug',
            ])
            ->when(
                $filters['from'] ?? null,
                fn ($query, int $cityId) => $query->where('departure_city_id', $cityId)
            )
            ->when(
                $filters['to'] ?? null,
                fn ($query, int $cityId) => $query->where('arrival_city_id', $cityId)
            )
            ->when(
                $filters['date'] ?? null,
                fn ($query, string $date) => $query->whereDate('departure_at', $date)
            )
            ->orderBy('departure_at')
            ->get();

        return view('connections.index', [
            'cities' => $cities,
            'trips' => $trips,
            'filters' => $filters,
        ]);
    }
}