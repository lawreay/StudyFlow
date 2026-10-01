<?php

namespace Tests\Feature;

use App\Models\GameNode;
use App\Models\GameWorld;
use App\Models\PlayerProgress;
use App\Models\RewardEvent;
use App\Models\User;
use App\Services\GameProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RewardEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_node_reward_is_recorded_once_and_xp_matches_player_progress(): void
    {
        $user = User::factory()->create();
        $world = GameWorld::create([
            'slug' => 'reward-test-world',
            'name' => 'Reward Test World',
            'is_active' => true,
        ]);
        $node = GameNode::create([
            'world_id' => $world->id,
            'slug' => 'reward-test-node',
            'name' => 'Reward Test Node',
            'position' => 1,
            'unlock_xp' => 0,
            'reward_xp' => 25,
            'is_start_node' => true,
        ]);
        $service = app(GameProgressService::class);

        $service->completeNode($user, $node->id);

        $event = RewardEvent::where('user_id', $user->id)->sole();
        $this->assertSame('node_completion', $event->type);
        $this->assertSame('game_node', $event->reference_type);
        $this->assertSame($node->id, $event->reference_id);
        $this->assertSame(25, $event->xp_amount);
        $this->assertNotNull($event->created_at);
        $this->assertSame(25, PlayerProgress::where('user_id', $user->id)->value('xp'));

        try {
            $service->completeNode($user, $node->id);
            $this->fail('A completed node must not grant another reward.');
        } catch (ValidationException) {
            $this->assertSame(1, RewardEvent::where('user_id', $user->id)->count());
            $this->assertSame(25, PlayerProgress::where('user_id', $user->id)->value('xp'));
        }
    }
}