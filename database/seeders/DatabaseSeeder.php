<?php

namespace Database\Seeders;

use Database\Seeders\Tests\AuthControllerSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        if (app()->environment('testing')) {
            $this->call(AuthControllerSeeder::class);
        } else {
            $this->call(DevelopmentSeeder::class);
        }
    }
}
