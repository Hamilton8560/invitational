<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Team;
use App\Models\TeamPlayer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\TeamPlayerController
 */
final class TeamPlayerControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        $teamPlayers = TeamPlayer::factory()->count(3)->create();

        $response = $this->get(route('team-players.index'));

        $response->assertOk();
        $response->assertViewIs('teamPlayer.index');
        $response->assertViewHas('teamPlayers');
    }


    #[Test]
    public function create_displays_view(): void
    {
        $response = $this->get(route('team-players.create'));

        $response->assertOk();
        $response->assertViewIs('teamPlayer.create');
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\TeamPlayerController::class,
            'store',
            \App\Http\Requests\TeamPlayerStoreRequest::class
        );
    }

    #[Test]
    public function store_saves_and_redirects(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $emergency_contact_name = fake()->word();
        $emergency_contact_phone = fake()->word();
        $waiver_signed = fake()->boolean();

        $response = $this->post(route('team-players.store'), [
            'team_id' => $team->id,
            'user_id' => $user->id,
            'emergency_contact_name' => $emergency_contact_name,
            'emergency_contact_phone' => $emergency_contact_phone,
            'waiver_signed' => $waiver_signed,
        ]);

        $teamPlayers = TeamPlayer::query()
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->where('emergency_contact_name', $emergency_contact_name)
            ->where('emergency_contact_phone', $emergency_contact_phone)
            ->where('waiver_signed', $waiver_signed)
            ->get();
        $this->assertCount(1, $teamPlayers);
        $teamPlayer = $teamPlayers->first();

        $response->assertRedirect(route('teamPlayers.index'));
        $response->assertSessionHas('teamPlayer.id', $teamPlayer->id);
    }


    #[Test]
    public function show_displays_view(): void
    {
        $teamPlayer = TeamPlayer::factory()->create();

        $response = $this->get(route('team-players.show', $teamPlayer));

        $response->assertOk();
        $response->assertViewIs('teamPlayer.show');
        $response->assertViewHas('teamPlayer');
    }


    #[Test]
    public function edit_displays_view(): void
    {
        $teamPlayer = TeamPlayer::factory()->create();

        $response = $this->get(route('team-players.edit', $teamPlayer));

        $response->assertOk();
        $response->assertViewIs('teamPlayer.edit');
        $response->assertViewHas('teamPlayer');
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\TeamPlayerController::class,
            'update',
            \App\Http\Requests\TeamPlayerUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $teamPlayer = TeamPlayer::factory()->create();
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $emergency_contact_name = fake()->word();
        $emergency_contact_phone = fake()->word();
        $waiver_signed = fake()->boolean();

        $response = $this->put(route('team-players.update', $teamPlayer), [
            'team_id' => $team->id,
            'user_id' => $user->id,
            'emergency_contact_name' => $emergency_contact_name,
            'emergency_contact_phone' => $emergency_contact_phone,
            'waiver_signed' => $waiver_signed,
        ]);

        $teamPlayer->refresh();

        $response->assertRedirect(route('teamPlayers.index'));
        $response->assertSessionHas('teamPlayer.id', $teamPlayer->id);

        $this->assertEquals($team->id, $teamPlayer->team_id);
        $this->assertEquals($user->id, $teamPlayer->user_id);
        $this->assertEquals($emergency_contact_name, $teamPlayer->emergency_contact_name);
        $this->assertEquals($emergency_contact_phone, $teamPlayer->emergency_contact_phone);
        $this->assertEquals($waiver_signed, $teamPlayer->waiver_signed);
    }


    #[Test]
    public function destroy_deletes_and_redirects(): void
    {
        $teamPlayer = TeamPlayer::factory()->create();

        $response = $this->delete(route('team-players.destroy', $teamPlayer));

        $response->assertRedirect(route('teamPlayers.index'));

        $this->assertModelMissing($teamPlayer);
    }


    #[Test]
    public function statements_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\TeamPlayerController::class,
            'statements',
            \App\Http\Requests\TeamPlayerStatementsRequest::class
        );
    }

    #[Test]
    public function statements_saves_and_redirects(): void
    {
        $user = User::factory()->create();
        $emergency_contact_name = fake()->word();
        $emergency_contact_phone = fake()->word();

        $response = $this->get(route('team-players.statements'), [
            'user_id' => $user->id,
            'emergency_contact_name' => $emergency_contact_name,
            'emergency_contact_phone' => $emergency_contact_phone,
        ]);

        $teamPlayers = TeamPlayer::query()
            ->where('user_id', $user->id)
            ->where('emergency_contact_name', $emergency_contact_name)
            ->where('emergency_contact_phone', $emergency_contact_phone)
            ->get();
        $this->assertCount(1, $teamPlayers);
        $teamPlayer = $teamPlayers->first();

        $response->assertRedirect(route('teams.show', ['teams' => $teams]));
        $response->assertSessionHas('teamPlayer.id', $teamPlayer->id);
    }
}
