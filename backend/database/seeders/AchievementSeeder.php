<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        Achievement::updateOrCreate(['slug' => 'digital-foundations-complete'], [
            'trigger_key' => 'world_complete:digital-foundations',
            'name' => 'Digital Foundations Complete',
            'description' => 'Completed every mission in the Digital Foundations learning world.',
            'icon' => '🏆',
        ]);
    }
}
