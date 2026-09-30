<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class QuestionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_filter_update_and_delete_questions(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $topic = $this->createTopic();
        $payload = $this->questionPayload($topic->id);

        $created = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/admin/questions', $payload)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.difficulty', 'Hard')
            ->assertJsonPath('data.options.0.is_correct', true);

        $questionId = $created->json('data.id');

        $this->getJson('/api/admin/questions?difficulty=Hard&topic_id='.$topic->id)
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->putJson("/api/admin/questions/{$questionId}", [
            ...$payload,
            'question_text' => 'Updated question?',
        ])
            ->assertOk()
            ->assertJsonPath('data.question_text', 'Updated question?');

        $this->deleteJson("/api/admin/questions/{$questionId}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('questions', ['id' => $questionId]);
    }

    public function test_students_cannot_manage_or_browse_question_bank(): void
    {
        $student = User::factory()->create();
        $topic = $this->createTopic();

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/admin/questions')
            ->assertForbidden();

        $this->postJson('/api/admin/questions', $this->questionPayload($topic->id))
            ->assertForbidden();

        $this->getJson('/api/questions')
            ->assertNotFound();
    }

    public function test_question_requires_exactly_one_correct_option(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $topic = $this->createTopic();
        $payload = $this->questionPayload($topic->id);
        $payload['options'][1]['is_correct'] = true;

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/admin/questions', $payload)
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('questions', 0);
    }

    public function test_csv_import_validates_all_rows_before_saving_any(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $topic = $this->createTopic();
        $csv = "question_text,difficulty,points,option_a,option_b,option_c,option_d,correct_option\n".
            "Valid row,Easy,2,A,B,C,D,a\n".
            "Invalid row,Expert,2,A,B,C,D,e\n";

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/topics/{$topic->id}/questions/import", [
                'file' => UploadedFile::fake()->createWithContent('questions.csv', $csv),
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('questions', 0);
    }

    public function test_admin_can_import_valid_csv_questions(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $topic = $this->createTopic();
        $csv = "question_text,difficulty,points,option_a,option_b,option_c,option_d,correct_option\n".
            "What is 2 + 2?,Easy,2,3,4,5,6,b\n";

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/topics/{$topic->id}/questions/import", [
                'file' => UploadedFile::fake()->createWithContent('questions.csv', $csv),
            ])
            ->assertCreated()
            ->assertJsonPath('data.imported_count', 1);

        $this->assertDatabaseHas('questions', [
            'topic_id' => $topic->id,
            'question_text' => 'What is 2 + 2?',
            'difficulty' => 'Easy',
        ]);
    }

    private function createTopic(): Topic
    {
        $subject = Subject::create(['name' => 'Networking']);

        return Topic::create(['subject_id' => $subject->id, 'name' => 'OSI Model']);
    }

    private function questionPayload(int $topicId): array
    {
        return [
            'topic_id' => $topicId,
            'question_text' => 'Which layer routes packets?',
            'difficulty' => 'Hard',
            'points' => 3,
            'explanation' => 'The network layer routes packets.',
            'options' => [
                ['option_text' => 'Network', 'is_correct' => true],
                ['option_text' => 'Physical', 'is_correct' => false],
            ],
        ];
    }
}