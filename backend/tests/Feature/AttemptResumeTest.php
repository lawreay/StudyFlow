<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttemptResumeTest extends TestCase
{
    use RefreshDatabase;

    public function test_unfinished_attempt_resumes_with_saved_position_and_timer(): void
    {
        $user = User::factory()->create();
        $subject = Subject::create(['name' => 'Networking']);
        $topic = Topic::create(['subject_id' => $subject->id, 'name' => 'Routing']);
        $questions = collect([1, 2])->map(function () use ($subject, $topic): Question {
            $question = Question::create([
                'subject_id' => $subject->id,
                'topic_id' => $topic->id,
                'question_text' => 'Question?',
                'points' => 1,
            ]);
            $question->options()->createMany([
                ['option_text' => 'Correct', 'is_correct' => true],
                ['option_text' => 'Wrong', 'is_correct' => false],
            ]);

            return $question->load('options');
        });

        $this->actingAs($user, 'sanctum');
        $started = $this->postJson('/api/attempts', ['topic_id' => $topic->id])
            ->assertCreated()
            ->assertJsonPath('data.current_question_index', 0)
            ->json('data');

        $this->patchJson("/api/attempts/{$started['id']}/position", ['current_question_index' => 1])
            ->assertOk()
            ->assertJsonPath('data.current_question_index', 1);

        $this->travel(95)->seconds();
        $resumed = $this->postJson('/api/attempts', ['topic_id' => $topic->id])
            ->assertOk()
            ->assertJsonPath('data.id', $started['id'])
            ->assertJsonPath('data.current_question_index', 1)
            ->assertJsonPath('data.elapsed_seconds', 95)
            ->json('data');

        $this->postJson("/api/attempts/{$resumed['id']}/answers", [
            'question_id' => $questions[0]->id,
            'selected_option_id' => $questions[0]->options[0]->id,
        ])->assertOk();

        $completedResponse = $this->postJson("/api/attempts/{$resumed['id']}/answers", [
            'question_id' => $questions[1]->id,
            'selected_option_id' => $questions[1]->options[0]->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.attempt.is_completed', true);

        $completed = $completedResponse->json('data.attempt');
        $this->assertGreaterThanOrEqual(95, $completed['duration_seconds']);
        $this->assertNotNull($completed['completed_at']);
    }

    public function test_users_cannot_change_another_users_attempt_position(): void
    {
        $subject = Subject::create(['name' => 'Networking']);
        $topic = Topic::create(['subject_id' => $subject->id, 'name' => 'Routing']);
        $question = Question::create([
            'subject_id' => $subject->id,
            'topic_id' => $topic->id,
            'question_text' => 'Question?',
            'points' => 1,
        ]);
        $question->options()->createMany([
            ['option_text' => 'Correct', 'is_correct' => true],
            ['option_text' => 'Wrong', 'is_correct' => false],
        ]);
        $attemptId = $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/attempts', ['topic_id' => $topic->id])
            ->assertCreated()
            ->json('data.id');

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->patchJson("/api/attempts/{$attemptId}/position", ['current_question_index' => 0])
            ->assertNotFound();
    }
}