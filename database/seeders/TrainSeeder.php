<?php

namespace Database\Seeders;

use App\Models\Train;
use Illuminate\Database\Seeder;

class TrainSeeder extends Seeder
{
    public function run(): void
    {
        $trains = [
            [
                'name' => 'RailTicket Express 160',
                'code' => 'RT-EXP-160',
                'type' => 'express',
                'wagon_count' => 4,
                'seats_per_wagon' => 40,
                'wagon_classes' => ['1' => 'first', '2' => 'second', '3' => 'second', '4' => 'second'],
            ],
            [
                'name' => 'RailTicket InterCity 120',
                'code' => 'RT-IC-120',
                'type' => 'intercity',
                'wagon_count' => 5,
                'seats_per_wagon' => 44,
                'wagon_classes' => ['1' => 'first', '2' => 'second', '3' => 'second', '4' => 'second', '5' => 'second'],
            ],
            [
                'name' => 'RailTicket Regional 90',
                'code' => 'RT-REG-090',
                'type' => 'regional',
                'wagon_count' => 3,
                'seats_per_wagon' => 36,
                'wagon_classes' => ['1' => 'second', '2' => 'second', '3' => 'second'],
            ],
        ];

        foreach ($trains as $train) {
            Train::updateOrCreate(
                ['code' => $train['code']],
                $train,
            );
        }
    }
}
