<?php

namespace Database\Seeders;

use App\Models\GameNode;
use App\Models\GameWorld;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class GameNodeSeeder extends Seeder
{
    public function run(): void
    {
        $world = GameWorld::where('slug', 'digital-foundations')->firstOrFail();
        $topics = [
            'computer-basics' => $this->findTopicId('Hardware', 'CPU'),
            'internet-fundamentals' => $this->findTopicId('Computer Networking', 'TCP/IP'),
            'networking-basics' => $this->findTopicId('Computer Networking', 'Network Devices'),
            'programming-introduction' => $this->findTopicId('Programming', 'Variables'),
        ];

        $nodes = [
            [
                'slug' => 'computer-basics',
                'name' => 'Computer Basics',
                'description' => 'Learn the essential parts and functions of a computer.',
                'position' => 1,
                'unlock_xp' => 0,
                'reward_xp' => 10,
                'topic_id' => $topics['computer-basics'],
                'required_score' => null,
                'is_start_node' => true,
            ],
            [
                'slug' => 'internet-fundamentals',
                'name' => 'Internet Fundamentals',
                'description' => 'Explore how devices communicate across the internet.',
                'position' => 2,
                'unlock_xp' => 10,
                'reward_xp' => 15,
                'topic_id' => $topics['internet-fundamentals'],
                'required_score' => null,
                'is_start_node' => false,
            ],
            [
                'slug' => 'networking-basics',
                'name' => 'Networking Basics',
                'description' => 'Understand local networks, switches, and routers.',
                'position' => 3,
                'unlock_xp' => 25,
                'reward_xp' => 20,
                'topic_id' => $topics['networking-basics'],
                'required_score' => 2,
                'is_start_node' => false,
            ],
            [
                'slug' => 'cybersecurity-basics',
                'name' => 'Cybersecurity Basics',
                'description' => 'Recognize essential practices for safer computing.',
                'position' => 4,
                'unlock_xp' => 45,
                'reward_xp' => 25,
                'topic_id' => null,
                'required_score' => null,
                'is_start_node' => false,
            ],
            [
                'slug' => 'programming-introduction',
                'name' => 'Programming Introduction',
                'description' => 'Meet the building blocks used to write programs.',
                'position' => 5,
                'unlock_xp' => 70,
                'reward_xp' => 30,
                'topic_id' => $topics['programming-introduction'],
                'required_score' => null,
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

    private function findTopicId(string $subjectName, string $topicName): ?int
    {
        return Topic::where('name', $topicName)
            ->whereHas('subject', fn ($query) => $query->where('name', $subjectName))
            ->value('id');
    }
}