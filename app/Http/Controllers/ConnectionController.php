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
        $filters = array_merge(
            [
                'from' => null,
                'to' => null,
                'date' => null,
            ],
            $request->validate([
                'from' => ['nullable', 'integer', 'exists:cities,id'],
                'to' => ['nullable', 'integer', 'exists:cities,id', 'different:from'],
                'date' => ['nullable', 'date_format:Y-m-d'],
            ])
        );

        $cities = City::active()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $hasSearched = filled($filters['from']) && filled($filters['to']);

        $trips = collect();

        if ($hasSearched) {
            $trips = Trip::query()
                ->visibleInDemo()
                ->with([
                    'train:id,name,code,type,wagon_count,seats_per_wagon,wagon_classes',
                    'departureCity:id,name,slug',
                    'arrivalCity:id,name,slug',
                    'wagons.seats',
                ])
                ->where('departure_city_id', $filters['from'])
                ->where('arrival_city_id', $filters['to'])
                ->when(
                    $filters['date'],
                    fn ($query, string $date) => $query->whereDate('departure_at', $date)
                )
                ->orderBy('departure_at')
                ->get();
        }

        $demoRoutePairs = [
            ['Gdańsk', 'Poznań'],
            ['Poznań', 'Wrocław'],
            ['Wrocław', 'Kraków'],
            ['Kraków', 'Warszawa'],
            ['Warszawa', 'Olsztyn'],
        ];

        return view('connections.index', [
            'cities' => $cities,
            'trips' => $trips,
            'filters' => $filters,
            'hasSearched' => $hasSearched,
            'demoRoutePairs' => $demoRoutePairs,
        ]);
    }
}