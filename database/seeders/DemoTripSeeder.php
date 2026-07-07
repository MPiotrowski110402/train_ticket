<?php

namespace Database\Seeders;

use App\Services\TripSchedulerService;
use Illuminate\Database\Seeder;

class DemoTripSeeder extends Seeder
{
    public function run(): void
    {
        app(TripSchedulerService::class)->rebuildDemoPool();
    }
}
