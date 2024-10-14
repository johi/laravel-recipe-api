<?php

namespace Database\Seeders;

use Database\Seeders\Tests\TestSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment('testing')) {
            $this->call(TestSeeder::class);
        } else {
            $this->call(DevelopmentSeeder::class);
        }
    }
}
