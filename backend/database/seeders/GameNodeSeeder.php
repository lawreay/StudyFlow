<?php

namespace Database\Seeders;

use App\Models\GameNode;
use App\Models\GameWorld;
use Illuminate\Database\Seeder;

class GameNodeSeeder extends Seeder
{
    public function run(): void
    {
        $world = GameWorld::where('slug', 'digital-foundations')->firstOrFail();

        $nodes = [
            [
                'slug' => 'computer-basics',
                'name' => 'Computer Basics',
                'description' => 'Learn the essential parts and functions of a computer.',
                'position' => 1,
                'unlock_xp' => 0,
                'reward_xp' => 10,
                'is_start_node' => true,
            ],
            [
                'slug' => 'internet-fundamentals',
                'name' => 'Internet Fundamentals',
                'description' => 'Explore how devices communicate across the internet.',
                'position' => 2,
                'unlock_xp' => 10,
                'reward_xp' => 15,
                'is_start_node' => false,
            ],
            [
                'slug' => 'networking-basics',
                'name' => 'Networking Basics',
                'description' => 'Understand local networks, switches, and routers.',
                'position' => 3,
                'unlock_xp' => 25,
                'reward_xp' => 20,
                'is_start_node' => false,
            ],
            [
                'slug' => 'cybersecurity-basics',
                'name' => 'Cybersecurity Basics',
                'description' => 'Recognize essential practices for safer computing.',
                'position' => 4,
                'unlock_xp' => 45,
                'reward_xp' => 25,
                'is_start_node' => false,
            ],
            [
                'slug' => 'programming-introduction',
                'name' => 'Programming Introduction',
                'description' => 'Meet the building blocks used to write programs.',
                'position' => 5,
                'unlock_xp' => 70,
                'reward_xp' => 30,
                'is_start_node' => false,
            ],
        ];

        foreach ($nodes as $node) {
            GameNode::updateOrCreate(
                ['world_id' => $world->id, 'slug' => $node['slug']],
                $node,
            );
        }
    }
}