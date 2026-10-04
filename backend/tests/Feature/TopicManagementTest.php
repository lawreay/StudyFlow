<?php

namespace Tests\Feature;

use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopicManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_a_topic_and_its_lesson_briefing(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $subject = Subject::create(['name' => 'Networking']);
        $topic = Topic::create([
            'subject_id' => $subject->id,
            'name' => 'Routing',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/admin/topics/{$topic->id}", [
                'name' => 'Routing Basics',
                'description' => 'Learn how packets find a path.',
                'lesson_title' => 'Routing rescue mission',
                'lesson_summary' => 'A packet needs the right exit route.',
                'lesson_content' => [
                    ['heading' => 'The big idea', 'body' => 'Routers move traffic between networks.'],
                    ['heading' => 'Mission clue', 'body' => 'Find the device that connects separate networks.'],
                ],
                'lesson_practice' => [
                    'question' => 'Which device selects a route?',
                    'options' => [
                        ['id' => 'router', 'label' => 'Router'],
                        ['id' => 'switch', 'label' => 'Switch'],
                    ],
                    'correct_option_id' => 'router',
                    'correct_feedback' => 'Correct. Routers connect networks.',
                    'incorrect_feedback' => 'Try again. Think about paths between networks.',
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Routing Basics')
            ->assertJsonPath('data.subject_name', 'Networking')
            ->assertJsonPath('data.lesson_content.1.heading', 'Mission clue')
            ->assertJsonPath('data.lesson_practice.correct_option_id', 'router');

        $this->assertDatabaseHas('topics', [
            'id' => $topic->id,
            'name' => 'Routing Basics',
            'lesson_title' => 'Routing rescue mission',
        ]);
    }

    public function test_students_cannot_manage_topic_content(): void
    {
        $topic = Topic::create([
            'subject_id' => Subject::create(['name' => 'Networking'])->id,
            'name' => 'Routing',
        ]);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson('/api/admin/topics')
            ->assertForbidden();

        $this->putJson("/api/admin/topics/{$topic->id}", ['name' => 'Changed'])
            ->assertForbidden();
    }
}
