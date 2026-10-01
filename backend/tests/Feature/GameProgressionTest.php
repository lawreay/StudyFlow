<?php

namespace Tests\Feature;

use App\Models\GameNode;
use App\Models\GameWorld;
use App\Models\PlayerNodeProgress;
use App\Models\PlayerProgress;
use App\Models\User;
use App\Services\GameProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GameProgressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_node_unlock_requires_xp_threshold_and_previous_node_completion(): void
    {
        $user = User::factory()->create();
        [$first, $second] = $this->createWorldNodes();
        PlayerProgress::create(['user_id' => $user->id, 'xp' => 10, 'level' => 1]);
        $service = app(GameProgressService::class);

        $this->assertTrue($service->isNodeUnlocked($user, $first));
        $this->assertFalse($service->isNodeUnlocked($user, $second));

        $service->completeNode($user, $first->id);

        $this->assertTrue($service->isNodeUnlocked($user, $second));
    }

    public function test_completing_node_saves_progress_and_awards_xp_once(): void
    {
        $user = User::factory()->create();
        [$first] = $this->createWorldNodes();
        $progress = PlayerProgress::create([
            'user_id' => $user->id,
            'xp' => 90,
            'level' => 1,
            'completed_questions' => 4,
            'accuracy' => 75,
            'score' => 12,
        ]);
        $service = app(GameProgressService::class);

        $result = $service->completeNode($user, $first->id);

        $this->assertNotNull($result['node_progress']->completed_at);
        $this->assertSame(105, $result['player_progress']->xp);
        $this->assertSame(2, $result['player_progress']->level);
        $this->assertSame(4, $result['player_progress']->completed_questions);
        $this->assertSame(75.0, (float) $result['player_progress']->accuracy);
        $this->assertSame(12, $result['player_progress']->score);
        $this->assertSame(1, PlayerNodeProgress::where('user_id', $user->id)->count());

        try {
            $service->completeNode($user, $first->id);
            $this->fail('Completing a node twice should be rejected.');
        } catch (ValidationException) {
            $this->assertSame(105, $progress->fresh()->xp);
        }
    }

    public function test_locked_node_cannot_be_completed_or_award_xp(): void
    {
        $user = User::factory()->create();
        [, $second] = $this->createWorldNodes();
        PlayerProgress::create(['user_id' => $user->id, 'xp' => 0, 'level' => 1]);

        try {
            app(GameProgressService::class)->completeNode($user, $second->id);
            $this->fail('A locked node should not be completable.');
        } catch (ValidationException) {
            $this->assertSame(0, PlayerProgress::where('user_id', $user->id)->value('xp'));
            $this->assertDatabaseMissing('player_node_progress', [
                'user_id' => $user->id,
                'game_node_id' => $second->id,
            ]);
        }
    }

    public function test_authenticated_world_api_returns_server_calculated_node_state(): void
    {
        $user = User::factory()->create();
        [$first, $second] = $this->createWorldNodes();

        $this->getJson('/api/game/worlds')
            ->assertUnauthorized();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/game/worlds')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.name', 'Test World')
            ->assertJsonPath('data.0.nodes.0.id', $first->id)
            ->assertJsonPath('data.0.nodes.0.is_unlocked', true)
            ->assertJsonPath('data.0.nodes.0.is_completed', false)
            ->assertJsonPath('data.0.nodes.1.id', $second->id)
            ->assertJsonPath('data.0.nodes.1.is_unlocked', false);
    }

    public function test_node_completion_api_awards_once_and_rejects_locked_or_duplicate_requests(): void
    {
        $user = User::factory()->create();
        [$first, $second] = $this->createWorldNodes();

        $this->postJson("/api/game/nodes/{$first->id}/complete")
            ->assertUnauthorized();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/game/nodes/{$second->id}/complete")
            ->assertUnprocessable();

        $this->postJson("/api/game/nodes/{$first->id}/complete")
            ->assertOk()
            ->assertJsonPath('data.node.is_completed', true)
            ->assertJsonPath('data.progress.xp', 15);

        $this->postJson("/api/game/nodes/{$first->id}/complete")
            ->assertUnprocessable();

        $this->assertSame(15, PlayerProgress::where('user_id', $user->id)->value('xp'));
    }

    private function createWorldNodes(): array
    {
        $world = GameWorld::create([
            'slug' => 'test-world',
            'name' => 'Test World',
            'is_active' => true,
        ]);
        $first = GameNode::create([
            'world_id' => $world->id,
            'slug' => 'first',
            'name' => 'First',
            'position' => 1,
            'unlock_xp' => 0,
            'reward_xp' => 15,
            'is_start_node' => true,
        ]);
        $second = GameNode::create([
            'world_id' => $world->id,
            'slug' => 'second',
            'name' => 'Second',
            'position' => 2,
            'unlock_xp' => 10,
            'reward_xp' => 20,
        ]);

        return [$first, $second];
    }
}