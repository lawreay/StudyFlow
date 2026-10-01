<?php

namespace Tests\Feature;

use App\Models\GameNode;
use App\Models\GameWorld;
use App\Models\PlayerNodeProgress;
use App\Models\PlayerProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameProgressSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_reports_current_world_completion_unlocked_nodes_and_existing_xp(): void
    {
        $user = User::factory()->create();
        $world = GameWorld::create([
            'slug' => 'summary-world',
            'name' => 'Summary World',
            'is_active' => true,
        ]);
        $nodes = collect([0, 1, 2])->map(fn (int $position) => GameNode::create([
            'world_id' => $world->id,
            'slug' => "node-{$position}",
            'name' => "Node {$position}",
            'position' => $position + 1,
            'unlock_xp' => 0,
            'reward_xp' => 10,
            'is_start_node' => $position === 0,
        ]));
        PlayerProgress::create(['user_id' => $user->id, 'xp' => 45, 'level' => 1]);
        PlayerNodeProgress::create([
            'user_id' => $user->id,
            'game_node_id' => $nodes[0]->id,
            'completed_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/game/progress')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.completed_nodes', 1)
            ->assertJsonPath('data.total_nodes', 3)
            ->assertJsonPath('data.completion_percentage', 33)
            ->assertJsonPath('data.xp', 45)
            ->assertJsonPath('data.current_world.name', 'Summary World')
            ->assertJsonPath('data.current_world.progress.completion_percentage', 33)
            ->assertJsonPath('data.next_nodes.0.id', $nodes[1]->id)
            ->assertJsonMissingPath('data.next_nodes.1');
    }

    public function test_summary_requires_authentication(): void
    {
        $this->getJson('/api/game/progress')->assertUnauthorized();
    }
}