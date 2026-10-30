<?php

namespace Database\Seeders;

use App\Services\ActivityLogger;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        ActivityLogger::$enabled = false;

        $this->call([
            UserSeeder::class,
            DemoSeeder::class,
        ]);

        ActivityLogger::$enabled = true;
    }
}
