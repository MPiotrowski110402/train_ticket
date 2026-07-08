<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name' => 'Gdańsk', 'slug' => 'gdansk'],
            ['name' => 'Poznań', 'slug' => 'poznan'],
            ['name' => 'Wrocław', 'slug' => 'wroclaw'],
            ['name' => 'Kraków', 'slug' => 'krakow'],
            ['name' => 'Warszawa', 'slug' => 'warszawa'],
            ['name' => 'Olsztyn', 'slug' => 'olsztyn'],
        ];

        $allowedSlugs = collect($cities)->pluck('slug')->all();

        City::query()
            ->whereNotIn('slug', $allowedSlugs)
            ->update(['is_active' => false]);

        foreach ($cities as $city) {
            City::updateOrCreate(
                ['slug' => $city['slug']],
                [
                    'name' => $city['name'],
                    'slug' => $city['slug'],
                    'is_active' => true,
                ],
            );
        }
    }
}