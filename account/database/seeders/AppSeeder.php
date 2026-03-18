<?php

namespace Database\Seeders;

use App\Models\App;
use Illuminate\Database\Seeder;

class AppSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        App::updateOrCreate(
            ['id' => '45e4cc21-1184-11f1-8bdf-3a3e507d609a'],
            [
                'name' => 'Selpics',
                'redirect_uri' => 'http://selpics.youfocus.test',
                'config' => '{}',
            ]
        );

        App::updateOrCreate(
            ['id' => 'c7a4b9f2-8b61-4c9d-9a2f-1e7d3b6a5c41'],
            [
                'name' => 'Fotovibe',
                'redirect_uri' => 'http://fotovibe.youfocus.test:8082',
                'config' => '{}',
            ]
        );
    }
}

