<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopicLessonTest extends TestCase
{
    use RefreshDatabase;

    public function test_lesson_completion_is_required_before_a_new_quiz_attempt(): void
    {
        $user = User::factory()->create();
        [$subject, $topic] = $this->createTopicWithLessonAndQuestion();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/subjects/{$subject->id}")
            ->assertOk()
            ->assertJsonPath('data.topics.0.has_lesson', true)
            ->assertJsonPath('data.topics.0.lesson_completed', false);

        $this->getJson("/api/topics/{$topic->id}/lesson")
            ->assertOk()
            ->assertJsonPath('data.lesson.title', 'Routing: your first mission')
            ->assertJsonPath('data.lesson.sections.0.heading', 'The big idea')
            ->assertJsonPath('data.lesson.practice.question', 'Which device routes packets?')
            ->assertJsonMissingPath('data.lesson.practice.correct_option_id')
            ->assertJsonPath('data.lesson.is_completed', false);

        $this->postJson('/api/attempts', ['topic_id' => $topic->id])
            ->assertUnprocessable()
            ->assertJsonPath('errors.lesson.0', 'Complete this topic’s lesson before starting its quiz.');

        $this->postJson("/api/topics/{$topic->id}/lesson/complete")
            ->assertUnprocessable()
            ->assertJsonPath('errors.practice.0', 'Pass the mission check before completing this lesson.');

        $this->postJson("/api/topics/{$topic->id}/lesson/practice", ['selected_option_id' => 'router'])
            ->assertOk()
            ->assertJsonPath('data.is_correct', true)
            ->assertJsonPath('data.is_practice_completed', true)
            ->assertJsonMissingPath('data.correct_option_id');

        $this->postJson("/api/topics/{$topic->id}/lesson/complete")
            ->assertOk()
            ->assertJsonPath('data.lesson.is_completed', true);

        $this->postJson('/api/attempts', ['topic_id' => $topic->id])
            ->assertCreated();

        $this->assertDatabaseHas('topic_lesson_completions', [
            'user_id' => $user->id,
            'topic_id' => $topic->id,
        ]);
    }

    public function test_lesson_completion_belongs_only_to_the_current_learner(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        [, $topic] = $this->createTopicWithLessonAndQuestion();

        $this->actingAs($firstUser, 'sanctum')
            ->postJson("/api/topics/{$topic->id}/lesson/practice", ['selected_option_id' => 'router'])
            ->assertOk()
            ->assertJsonPath('data.is_practice_completed', true);

        $this->postJson("/api/topics/{$topic->id}/lesson/complete")
            ->assertOk()
            ->assertJsonPath('data.lesson.is_completed', true);

        $this->actingAs($secondUser, 'sanctum')
            ->postJson("/api/topics/{$topic->id}/lesson/practice", ['selected_option_id' => 'switch'])
            ->assertOk()
            ->assertJsonPath('data.is_correct', false)
            ->assertJsonPath('data.is_practice_completed', false);

        $this->postJson("/api/topics/{$topic->id}/lesson/complete")
            ->assertUnprocessable()
            ->assertJsonPath('errors.practice.0', 'Pass the mission check before completing this lesson.');

        $this->getJson("/api/topics/{$topic->id}/lesson")
            ->assertOk()
            ->assertJsonPath('data.lesson.is_completed', false);
    }

    private function createTopicWithLessonAndQuestion(): array
    {
        $subject = Subject::create(['name' => 'Networking']);
        $topic = Topic::create([
            'subject_id' => $subject->id,
            'name' => 'Routing',
            'lesson_title' => 'Routing: your first mission',
            'lesson_summary' => 'Find the right path for a packet.',
            'lesson_content' => [
                ['heading' => 'The big idea', 'body' => 'Routers send traffic between networks.'],
            ],
            'lesson_practice' => [
                'question' => 'Which device routes packets?',
                'options' => [
                    ['id' => 'router', 'label' => 'Router'],
                    ['id' => 'switch', 'label' => 'Switch'],
                ],
                'correct_option_id' => 'router',
                'correct_feedback' => 'Correct. Routers send packets between networks.',
                'incorrect_feedback' => 'Try again. Switches connect devices inside a network.',
            ],
        ]);
        $question = Question::create([
            'subject_id' => $subject->id,
            'topic_id' => $topic->id,
            'question_text' => 'Which device routes packets?',
        ]);
        $question->options()->createMany([
            ['option_text' => 'Router', 'is_correct' => true],
            ['option_text' => 'Keyboard', 'is_correct' => false],
        ]);

        return [$subject, $topic];
    }
}
