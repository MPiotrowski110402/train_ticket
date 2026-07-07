<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name' => 'Opole', 'slug' => 'opole'],
            ['name' => 'Wrocław', 'slug' => 'wroclaw'],
            ['name' => 'Katowice', 'slug' => 'katowice'],
            ['name' => 'Kraków', 'slug' => 'krakow'],
            ['name' => 'Warszawa', 'slug' => 'warszawa'],
            ['name' => 'Poznań', 'slug' => 'poznan'],
            ['name' => 'Gdańsk', 'slug' => 'gdansk'],
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(
                ['slug' => $city['slug']],
                [...$city, 'is_active' => true],
            );
        }
    }
}
