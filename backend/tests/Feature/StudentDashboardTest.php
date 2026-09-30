<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_progress_quiz_average_recent_activity_and_resume_attempt(): void
    {
        $user = User::factory()->create();
        $subject = Subject::create(['name' => 'Networking']);
        $topic = $subject->topics()->create(['name' => 'Routing']);
        $questions = collect([1, 1])->map(function () use ($subject, $topic): Question {
            $question = Question::create([
                'subject_id' => $subject->id,
                'topic_id' => $topic->id,
                'question_text' => 'Question?',
                'points' => 2,
            ]);
            $question->options()->createMany([
                ['option_text' => 'Correct', 'is_correct' => true],
                ['option_text' => 'Wrong', 'is_correct' => false],
            ]);

            return $question->load('options');
        });

        $this->actingAs($user, 'sanctum');
        $attempt = $this->postJson('/api/attempts', ['topic_id' => $topic->id])
            ->assertCreated()
            ->json('data');

        $activeDashboard = $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.completed_quizzes', 0)
            ->assertJsonPath('data.active_attempt.id', $attempt['id']);

        $this->postJson("/api/attempts/{$attempt['id']}/complete")
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->postJson("/api/attempts/{$attempt['id']}/answers", [
            'question_id' => $questions[0]->id,
            'selected_option_id' => $questions[0]->options[0]->id,
        ])->assertOk();

        $this->postJson("/api/attempts/{$attempt['id']}/answers", [
            'question_id' => $questions[1]->id,
            'selected_option_id' => $questions[1]->options[1]->id,
        ])->assertOk();

        $this->postJson("/api/attempts/{$attempt['id']}/complete")
            ->assertOk()
            ->assertJsonPath('data.attempt.is_completed', true);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.progress.xp', 10)
            ->assertJsonPath('data.progress.level', 1)
            ->assertJsonPath('data.progress.completed_questions', 2)
            ->assertJsonPath('data.completed_quizzes', 1)
            ->assertJsonPath('data.average_score_percent', 50)
            ->assertJsonPath('data.recent_activity.0.topic_name', 'Routing')
            ->assertJsonPath('data.recent_activity.0.score', 2)
            ->assertJsonPath('data.active_attempt', null);
    }
}