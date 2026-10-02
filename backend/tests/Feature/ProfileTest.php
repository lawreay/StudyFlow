<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_learner_can_update_their_display_name(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/me', ['name' => 'Lawrence Banda'])
            ->assertOk()
            ->assertJsonPath('data.user.name', 'Lawrence Banda')
            ->assertJsonPath('data.user.email', $user->email);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Lawrence Banda',
        ]);
    }

    public function test_profile_update_requires_authentication_and_a_name(): void
    {
        $this->patchJson('/api/me', ['name' => 'Lawrence Banda'])
            ->assertUnauthorized();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->patchJson('/api/me', ['name' => ''])
            ->assertUnprocessable()
            ->assertJsonPath('errors.name.0', 'The name field is required.');
    }
}
