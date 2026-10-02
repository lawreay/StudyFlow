<?php

namespace Tests\Feature;

use App\Models\GameNode;
use App\Models\GameWorld;
use App\Models\PlayerNodeProgress;
use App\Models\PlayerProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_learner_can_only_issue_a_certificate_after_completing_every_world_mission(): void
    {
        $user = User::factory()->create(['name' => 'Lawrence Banda']);
        $world = $this->createWorld();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/certificates/worlds/{$world->id}")
            ->assertUnprocessable()
            ->assertJsonPath('errors.world.0', 'Complete every mission in this learning world before claiming its certificate.');

        $this->completeWorldFor($user, $world);

        $issuedCertificate = $this->postJson("/api/certificates/worlds/{$world->id}")
            ->assertCreated()
            ->assertJsonPath('data.learner_name', 'Lawrence Banda')
            ->assertJsonPath('data.program_name', 'Digital Foundations')
            ->json('data');

        $this->assertStringStartsWith('SF-'.now()->format('Y').'-', $issuedCertificate['certificate_number']);
        $this->assertDatabaseHas('certificates', [
            'user_id' => $user->id,
            'game_world_id' => $world->id,
            'certificate_number' => $issuedCertificate['certificate_number'],
        ]);

        $this->getJson("/api/certificates/verify/{$issuedCertificate['certificate_number']}")
            ->assertOk()
            ->assertJsonPath('data.is_valid', true)
            ->assertJsonPath('data.learner_name', 'Lawrence Banda')
            ->assertJsonPath('data.program_name', 'Digital Foundations');
    }

    public function test_certificates_are_private_except_for_their_public_verification_record(): void
    {
        $owner = User::factory()->create(['name' => 'Certificate Owner']);
        $otherUser = User::factory()->create();
        $world = $this->createWorld();
        $this->completeWorldFor($owner, $world);

        $certificateNumber = $this->actingAs($owner, 'sanctum')
            ->postJson("/api/certificates/worlds/{$world->id}")
            ->assertCreated()
            ->json('data.certificate_number');

        $this->actingAs($otherUser, 'sanctum')
            ->getJson('/api/certificates')
            ->assertOk()
            ->assertJsonCount(0, 'data.certificates');

        $this->getJson("/api/certificates/verify/{$certificateNumber}")
            ->assertOk()
            ->assertJsonPath('data.learner_name', 'Certificate Owner');
    }

    private function createWorld(): GameWorld
    {
        $world = GameWorld::create([
            'slug' => 'digital-foundations',
            'name' => 'Digital Foundations',
            'is_active' => true,
        ]);

        foreach ([1, 2] as $position) {
            GameNode::create([
                'world_id' => $world->id,
                'slug' => "mission-{$position}",
                'name' => "Mission {$position}",
                'position' => $position,
                'unlock_xp' => 0,
                'reward_xp' => 10,
                'is_start_node' => $position === 1,
            ]);
        }

        return $world;
    }

    private function completeWorldFor(User $user, GameWorld $world): void
    {
        PlayerProgress::create(['user_id' => $user->id, 'xp' => 100, 'level' => 2]);

        $world->nodes->each(fn (GameNode $node) => PlayerNodeProgress::create([
            'user_id' => $user->id,
            'game_node_id' => $node->id,
            'completed_at' => now(),
        ]));
    }
}
