<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningAttemptTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_complete_attempt_and_earn_server_calculated_score_and_xp(): void
    {
        $user = User::factory()->create();
        [$topic, $questions] = $this->createTopicWithQuestions();

        $attemptResponse = $this->actingAs($user, 'sanctum')
            ->postJson('/api/attempts', ['topic_id' => $topic->id])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_questions', 2)
            ->assertJsonCount(2, 'data.questions')
            ->assertJsonMissingPath('data.questions.0.options.0.is_correct');

        $attemptId = $attemptResponse->json('data.id');

        $this->postJson("/api/attempts/{$attemptId}/answers", [
            'question_id' => $questions[0]->id,
            'selected_option_id' => $questions[0]->options[0]->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.answer.is_correct', true)
            ->assertJsonPath('data.answer.marks_earned', 3)
            ->assertJsonPath('data.attempt.is_completed', false);

        $this->postJson("/api/attempts/{$attemptId}/answers", [
            'question_id' => $questions[1]->id,
            'selected_option_id' => $questions[1]->options[1]->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.answer.is_correct', false)
            ->assertJsonPath('data.answer.marks_earned', 0)
            ->assertJsonPath('data.attempt.is_completed', true)
            ->assertJsonPath('data.attempt.score', 3)
            ->assertJsonPath('data.attempt.correct_answers', 1)
            ->assertJsonPath('data.attempt.xp_earned', 10);

        $this->assertDatabaseHas('attempt_answers', [
            'attempt_id' => $attemptId,
            'question_id' => $questions[0]->id,
            'selected_option_id' => $questions[0]->options[0]->id,
            'is_correct' => true,
            'marks_earned' => 3,
        ]);
        $this->assertDatabaseHas('player_progress', [
            'user_id' => $user->id,
            'xp' => 10,
            'level' => 1,
            'completed_questions' => 2,
            'accuracy' => 50,
        ]);
    }

    public function test_question_endpoints_require_authentication_and_do_not_expose_answer_keys(): void
    {
        [$topic, $questions] = $this->createTopicWithQuestions();

        $this->getJson('/api/questions')->assertUnauthorized();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson("/api/questions/{$questions[0]->id}")
            ->assertOk()
            ->assertJsonPath('data.points', 3)
            ->assertJsonMissingPath('data.options.0.is_correct');
    }

    public function test_user_cannot_submit_the_same_question_twice(): void
    {
        $user = User::factory()->create();
        [$topic, $questions] = $this->createTopicWithQuestions();
        $attemptId = $this->actingAs($user, 'sanctum')
            ->postJson('/api/attempts', ['topic_id' => $topic->id])
            ->assertCreated()
            ->json('data.id');

        $answer = [
            'question_id' => $questions[0]->id,
            'selected_option_id' => $questions[0]->options[0]->id,
        ];

        $this->postJson("/api/attempts/{$attemptId}/answers", $answer)->assertOk();
        $this->postJson("/api/attempts/{$attemptId}/answers", $answer)->assertUnprocessable();
    }

    private function createTopicWithQuestions(): array
    {
        $subject = Subject::create(['name' => 'Networking']);
        $topic = Topic::create([
            'subject_id' => $subject->id,
            'name' => 'OSI Model',
        ]);

        $questions = collect([3, 2])->map(function (int $points) use ($subject, $topic): Question {
            $question = Question::create([
                'subject_id' => $subject->id,
                'topic_id' => $topic->id,
                'question_text' => 'Sample question?',
                'points' => $points,
            ]);

            $question->options()->createMany([
                ['option_text' => 'Correct option', 'is_correct' => true],
                ['option_text' => 'Incorrect option', 'is_correct' => false],
            ]);

            return $question->load('options');
        });

        return [$topic, $questions];
    }
}