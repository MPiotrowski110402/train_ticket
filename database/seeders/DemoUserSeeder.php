<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'testowy.user@test.pl',
            ],
            [
                'name' => 'Testowy User',
                'email' => 'testowy.user@test.pl',
                'phone' => '500 600 700',
                'password' => Hash::make('..........'),
            ]
        );
    }
}