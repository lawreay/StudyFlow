<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_read_content_summary_and_students_are_denied(): void
    {
        $subject = Subject::create(['name' => 'Networking']);
        $topic = Topic::create(['subject_id' => $subject->id, 'name' => 'Routing']);
        Question::create([
            'subject_id' => $subject->id,
            'topic_id' => $topic->id,
            'question_text' => 'Question?',
            'points' => 1,
        ]);

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson('/api/admin/dashboard')
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['is_admin' => true]), 'sanctum')
            ->getJson('/api/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('data.subjects_count', 1)
            ->assertJsonPath('data.topics_count', 1)
            ->assertJsonPath('data.questions_count', 1)
            ->assertJsonPath('data.users_count', 2);
    }

    public function test_registration_cannot_grant_admin_access(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Student',
            'email' => 'student@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_admin' => true,
        ])->assertOk();

        $this->assertFalse($response->json('data.user.is_admin'));
        $this->assertDatabaseHas('users', ['email' => 'student@example.test', 'is_admin' => false]);
    }
}