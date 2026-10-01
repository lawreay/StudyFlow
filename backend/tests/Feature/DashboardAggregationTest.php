<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\GameNode;
use App\Models\GameWorld;
use App\Models\PlayerNodeProgress;
use App\Models\PlayerProgress;
use App\Models\Question;
use App\Models\RewardEvent;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAggregationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_aggregates_player_learning_game_and_reward_sources(): void
    {
        $user = User::factory()->create(['name' => 'Ada Learner']);
        $progress = PlayerProgress::create([
            'user_id' => $user->id,
            'xp' => 500,
            'level' => 3,
            'completed_questions' => 10,
            'accuracy' => 80,
            'score' => 22,
        ]);
        $subject = Subject::create(['name' => 'Networking']);
        $topic = Topic::create(['subject_id' => $subject->id, 'name' => 'Routing']);
        $questions = collect([1, 2])->map(fn () => Question::create([
            'subject_id' => $subject->id,
            'topic_id' => $topic->id,
            'question_text' => 'What forwards between networks?',
            'points' => 5,
        ]));
        $attempt = Attempt::create([
            'user_id' => $user->id,
            'subject_id' => $subject->id,
            'topic_id' => $topic->id,
            'started_at' => now()->subMinutes(4),
            'score' => 8,
            'correct_answers' => 1,
            'total_questions' => 2,
            'completed_at' => now()->subMinute(),
            'duration_seconds' => 180,
        ]);
        $attempt->questions()->attach($questions->mapWithKeys(
            fn (Question $question) => [$question->id => ['points' => 5]],
        )->all());

        $world = GameWorld::create([
            'slug' => 'dashboard-world',
            'name' => 'Dashboard World',
            'is_active' => true,
        ]);
        $nodes = collect([1, 2, 3])->map(fn (int $position) => GameNode::create([
            'world_id' => $world->id,
            'slug' => "dashboard-node-{$position}",
            'name' => "Dashboard Node {$position}",
            'position' => $position,
            'unlock_xp' => 0,
            'reward_xp' => 10,
            'is_start_node' => $position === 1,
        ]));
        PlayerNodeProgress::create([
            'user_id' => $user->id,
            'game_node_id' => $nodes[0]->id,
            'completed_at' => now(),
        ]);
        RewardEvent::create([
            'user_id' => $user->id,
            'type' => 'node_completion',
            'reference_type' => 'game_node',
            'reference_id' => $nodes[0]->id,
            'xp_amount' => 10,
            'created_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.player.name', 'Ada Learner')
            ->assertJsonPath('data.player.xp', $progress->xp)
            ->assertJsonPath('data.player.level', $progress->level)
            ->assertJsonPath('data.player.accuracy', 80)
            ->assertJsonPath('data.learning.completed_attempts', 1)
            ->assertJsonPath('data.learning.completed_topics', 1)
            ->assertJsonPath('data.learning.recent_attempts.0.id', $attempt->id)
            ->assertJsonPath('data.learning.recent_attempts.0.score', 8)
            ->assertJsonPath('data.game.world', 'Dashboard World')
            ->assertJsonPath('data.game.completed_nodes', 1)
            ->assertJsonPath('data.game.total_nodes', 3)
            ->assertJsonPath('data.game.completion_percentage', 33)
            ->assertJsonPath('data.game.next_nodes.0.id', $nodes[1]->id)
            ->assertJsonPath('data.rewards.0.type', 'node_completion')
            ->assertJsonPath('data.rewards.0.xp_amount', 10)
            ->assertJsonPath('data.progress.xp', 500)
            ->assertJsonPath('data.completed_quizzes', 1);
    }

    public function test_dashboard_api_rejects_unauthenticated_users(): void
    {
        $this->getJson('/api/dashboard')->assertUnauthorized();
    }
}