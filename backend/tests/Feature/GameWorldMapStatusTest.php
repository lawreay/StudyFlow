<?php

namespace Tests\Feature;

use App\Models\GameNode;
use App\Models\GameWorld;
use App\Models\PlayerNodeProgress;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameWorldMapStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_map_reports_completed_available_and_locked_with_topic_details(): void
    {
        $user = User::factory()->create();
        $subject = Subject::create(['name' => 'Networking']);
        $topic = Topic::create(['subject_id' => $subject->id, 'name' => 'Network Devices']);
        $world = GameWorld::create([
            'slug' => 'map-status-world',
            'name' => 'Map Status World',
            'is_active' => true,
        ]);
        $nodes = collect([1, 2, 3])->map(fn (int $position) => GameNode::create([
            'world_id' => $world->id,
            'topic_id' => $position === 1 ? $topic->id : null,
            'slug' => "map-node-{$position}",
            'name' => "Map Node {$position}",
            'position' => $position,
            'unlock_xp' => 0,
            'reward_xp' => 5,
            'required_score' => $position === 1 ? 3 : null,
            'is_start_node' => $position === 1,
        ]));
        PlayerNodeProgress::create([
            'user_id' => $user->id,
            'game_node_id' => $nodes[0]->id,
            'completed_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/game/worlds')
            ->assertOk()
            ->assertJsonPath('data.0.nodes.0.status', 'completed')
            ->assertJsonPath('data.0.nodes.0.topic.id', $topic->id)
            ->assertJsonPath('data.0.nodes.0.topic.name', 'Network Devices')
            ->assertJsonPath('data.0.nodes.0.required_score', 3)
            ->assertJsonPath('data.0.nodes.1.status', 'available')
            ->assertJsonPath('data.0.nodes.2.status', 'locked');
    }
}