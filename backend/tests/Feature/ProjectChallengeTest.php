<?php

namespace Tests\Feature;

use App\Models\ProjectChallenge;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectChallengeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_challenge_and_learner_can_submit_then_receive_review(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $learner = User::factory()->create();
        $topic = Topic::create([
            'subject_id' => Subject::create(['name' => 'Networking'])->id,
            'name' => 'Network Devices',
        ]);

        $challengeId = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/admin/project-challenges', $this->challengePayload($topic->id))
            ->assertCreated()
            ->assertJsonPath('data.is_active', true)
            ->json('data.id');

        $this->actingAs($learner, 'sanctum')
            ->getJson('/api/project-challenges')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Design a small office network')
            ->assertJsonPath('data.0.submission', null);

        $submissionId = $this->postJson("/api/project-challenges/{$challengeId}/submissions", [
            'project_url' => 'https://github.com/learner/office-network',
            'notes' => 'I included a router, switch, and IP plan.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'submitted')
            ->json('data.id');

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/project-submissions/{$submissionId}/review", [
                'status' => 'approved',
                'feedback' => 'Clear design and sensible addressing plan.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->actingAs($learner, 'sanctum')
            ->getJson("/api/project-challenges/{$challengeId}")
            ->assertOk()
            ->assertJsonPath('data.submission.status', 'approved')
            ->assertJsonPath('data.submission.feedback', 'Clear design and sensible addressing plan.');
    }

    public function test_students_cannot_manage_or_review_project_challenges(): void
    {
        $student = User::factory()->create();
        $challenge = ProjectChallenge::create($this->challengePayload());

        $this->actingAs($student, 'sanctum')
            ->postJson('/api/admin/project-challenges', $this->challengePayload())
            ->assertForbidden();

        $this->getJson("/api/admin/project-challenges/{$challenge->id}/submissions")
            ->assertForbidden();
    }

    private function challengePayload(?int $topicId = null): array
    {
        return [
            'topic_id' => $topicId,
            'title' => 'Design a small office network',
            'brief' => 'Plan a simple, safe network for a small office.',
            'requirements' => [
                'Include a router and a switch.',
                'Explain the IP address plan.',
            ],
            'is_active' => true,
        ];
    }
}
