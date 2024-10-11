<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            category::class, // php artisan db:seed --class=category
            topic::class, // php artisan db:seed --class=topic
            gen_user_roleandper::class // php artisan db:seed --class=gen_user_roleandper
        ]);
    }
}
