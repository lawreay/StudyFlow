<?php

namespace Tests\Feature;

use App\Models\GameNode;
use App\Models\GameWorld;
use App\Models\Question;
use App\Models\PlayerNodeProgress;
use App\Models\PlayerProgress;
use App\Models\Subject;
use App\Models\Topic;
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

    public function test_linked_node_requires_completed_topic_attempt_and_required_score(): void
    {
        $user = User::factory()->create();
        $subject = Subject::create(['name' => 'Networking']);
        $topic = Topic::create(['subject_id' => $subject->id, 'name' => 'Network Devices']);
        $questions = collect([1, 2])->map(function () use ($subject, $topic): Question {
            $question = Question::create([
                'subject_id' => $subject->id,
                'topic_id' => $topic->id,
                'question_text' => 'Which device routes between networks?',
                'points' => 1,
            ]);
            $question->options()->createMany([
                ['option_text' => 'Router', 'is_correct' => true],
                ['option_text' => 'Switch', 'is_correct' => false],
            ]);

            return $question->load('options');
        });
        [$first] = $this->createWorldNodes($topic->id, 2);
        $this->actingAs($user, 'sanctum');

        $this->postJson("/api/game/nodes/{$first->id}/complete")
            ->assertUnprocessable()
            ->assertJsonPath('errors.learning_activity.0', 'Complete the linked topic quiz before completing this node.');

        $lowScoreAttempt = $this->postJson('/api/attempts', ['topic_id' => $topic->id])
            ->assertCreated()
            ->json('data');
        $this->postJson("/api/attempts/{$lowScoreAttempt['id']}/answers", [
            'question_id' => $questions[0]->id,
            'selected_option_id' => $questions[0]->options[0]->id,
        ])->assertOk();
        $this->postJson("/api/attempts/{$lowScoreAttempt['id']}/answers", [
            'question_id' => $questions[1]->id,
            'selected_option_id' => $questions[1]->options[1]->id,
        ])->assertOk();
        $this->postJson("/api/attempts/{$lowScoreAttempt['id']}/complete")
            ->assertOk()
            ->assertJsonPath('data.attempt.score', 1);

        $this->postJson("/api/game/nodes/{$first->id}/complete")
            ->assertUnprocessable()
            ->assertJsonPath('errors.required_score.0', 'The completed topic quiz did not meet the required score.');

        $qualifyingAttempt = $this->postJson('/api/attempts', ['topic_id' => $topic->id])
            ->assertCreated()
            ->json('data');
        foreach ($questions as $question) {
            $this->postJson("/api/attempts/{$qualifyingAttempt['id']}/answers", [
                'question_id' => $question->id,
                'selected_option_id' => $question->options[0]->id,
            ])->assertOk();
        }
        $this->postJson("/api/attempts/{$qualifyingAttempt['id']}/complete")
            ->assertOk()
            ->assertJsonPath('data.attempt.score', 2);

        $this->postJson("/api/game/nodes/{$first->id}/complete")
            ->assertOk()
            ->assertJsonPath('data.node.is_completed', true)
            ->assertJsonPath('data.reward_xp', 15)
            ->assertJsonPath('data.progress.xp', 45)
            ->assertJsonPath('data.next_available_node.id', $first->id + 1);

        $this->postJson("/api/game/nodes/{$first->id}/complete")
            ->assertUnprocessable();

        $this->assertSame(45, PlayerProgress::where('user_id', $user->id)->value('xp'));
        $this->assertSame(1, PlayerNodeProgress::where('user_id', $user->id)->count());
    }

    private function createWorldNodes(?int $topicId = null, ?int $requiredScore = null): array
    {
        $world = GameWorld::create([
            'slug' => 'test-world',
            'name' => 'Test World',
            'is_active' => true,
        ]);
        $first = GameNode::create([
            'world_id' => $world->id,
            'topic_id' => $topicId,
            'slug' => 'first',
            'name' => 'First',
            'position' => 1,
            'unlock_xp' => 0,
            'reward_xp' => 15,
            'required_score' => $requiredScore,
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