<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\GameNode;
use App\Models\GameWorld;
use App\Models\PlayerProgress;
use App\Models\User;
use App\Services\GameProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementTest extends TestCase
{
    use RefreshDatabase;

    public function test_completing_a_world_awards_its_milestone_once(): void
    {
        $user = User::factory()->create();
        $world = GameWorld::create([
            'slug' => 'digital-foundations',
            'name' => 'Digital Foundations',
            'is_active' => true,
        ]);
        $achievement = Achievement::create([
            'slug' => 'digital-foundations-complete',
            'trigger_key' => 'world_complete:digital-foundations',
            'name' => 'Digital Foundations Complete',
            'description' => 'Completed every mission.',
            'icon' => '🏆',
        ]);
        PlayerProgress::create(['user_id' => $user->id, 'xp' => 100, 'level' => 2]);
        $firstNode = GameNode::create([
            'world_id' => $world->id,
            'slug' => 'first-mission',
            'name' => 'First mission',
            'position' => 1,
            'reward_xp' => 10,
            'is_start_node' => true,
        ]);
        $lastNode = GameNode::create([
            'world_id' => $world->id,
            'slug' => 'last-mission',
            'name' => 'Last mission',
            'position' => 2,
            'reward_xp' => 10,
        ]);

        $gameProgressService = $this->app->make(GameProgressService::class);
        $gameProgressService->completeNode($user, $firstNode->id);

        $this->assertDatabaseCount('user_achievements', 0);

        $gameProgressService->completeNode($user, $lastNode->id);

        $this->assertDatabaseHas('user_achievements', [
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
        ]);
        $this->assertDatabaseCount('user_achievements', 1);
    }
}
