<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SubjectSeeder::class,
            QuestionSeeder::class,
            GameWorldSeeder::class,
            GameNodeSeeder::class,
        ]);
    }
}
