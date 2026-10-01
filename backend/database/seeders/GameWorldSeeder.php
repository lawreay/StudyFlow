<?php

namespace Database\Seeders;

use App\Models\GameWorld;
use Illuminate\Database\Seeder;

class GameWorldSeeder extends Seeder
{
    public function run(): void
    {
        GameWorld::updateOrCreate(
            ['slug' => 'digital-foundations'],
            [
                'name' => 'Digital Foundations',
                'description' => 'Build practical digital knowledge one learning area at a time.',
                'is_active' => true,
            ],
        );
    }
}