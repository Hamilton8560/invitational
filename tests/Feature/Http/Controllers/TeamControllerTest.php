<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Division;
use App\Models\Event;
use App\Models\Owner;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\TeamController
 */
final class TeamControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        $teams = Team::factory()->count(3)->create();

        $response = $this->get(route('teams.index'));

        $response->assertOk();
        $response->assertViewIs('team.index');
        $response->assertViewHas('teams');
    }


    #[Test]
    public function create_displays_view(): void
    {
        $response = $this->get(route('teams.create'));

        $response->assertOk();
        $response->assertViewIs('team.create');
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\TeamController::class,
            'store',
            \App\Http\Requests\TeamStoreRequest::class
        );
    }

    #[Test]
    public function store_saves_and_redirects(): void
    {
        $event = Event::factory()->create();
        $division = Division::factory()->create();
        $owner = Owner::factory()->create();
        $name = fake()->name();
        $max_players = fake()->numberBetween(-10000, 10000);
        $current_players = fake()->numberBetween(-10000, 10000);

        $response = $this->post(route('teams.store'), [
            'event_id' => $event->id,
            'division_id' => $division->id,
            'owner_id' => $owner->id,
            'name' => $name,
            'max_players' => $max_players,
            'current_players' => $current_players,
        ]);

        $teams = Team::query()
            ->where('event_id', $event->id)
            ->where('division_id', $division->id)
            ->where('owner_id', $owner->id)
            ->where('name', $name)
            ->where('max_players', $max_players)
            ->where('current_players', $current_players)
            ->get();
        $this->assertCount(1, $teams);
        $team = $teams->first();

        $response->assertRedirect(route('teams.index'));
        $response->assertSessionHas('team.id', $team->id);
    }


    #[Test]
    public function show_displays_view(): void
    {
        $team = Team::factory()->create();

        $response = $this->get(route('teams.show', $team));

        $response->assertOk();
        $response->assertViewIs('team.show');
        $response->assertViewHas('team');
    }


    #[Test]
    public function edit_displays_view(): void
    {
        $team = Team::factory()->create();

        $response = $this->get(route('teams.edit', $team));

        $response->assertOk();
        $response->assertViewIs('team.edit');
        $response->assertViewHas('team');
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\TeamController::class,
            'update',
            \App\Http\Requests\TeamUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $team = Team::factory()->create();
        $event = Event::factory()->create();
        $division = Division::factory()->create();
        $owner = Owner::factory()->create();
        $name = fake()->name();
        $max_players = fake()->numberBetween(-10000, 10000);
        $current_players = fake()->numberBetween(-10000, 10000);

        $response = $this->put(route('teams.update', $team), [
            'event_id' => $event->id,
            'division_id' => $division->id,
            'owner_id' => $owner->id,
            'name' => $name,
            'max_players' => $max_players,
            'current_players' => $current_players,
        ]);

        $team->refresh();

        $response->assertRedirect(route('teams.index'));
        $response->assertSessionHas('team.id', $team->id);

        $this->assertEquals($event->id, $team->event_id);
        $this->assertEquals($division->id, $team->division_id);
        $this->assertEquals($owner->id, $team->owner_id);
        $this->assertEquals($name, $team->name);
        $this->assertEquals($max_players, $team->max_players);
        $this->assertEquals($current_players, $team->current_players);
    }


    #[Test]
    public function destroy_deletes_and_redirects(): void
    {
        $team = Team::factory()->create();

        $response = $this->delete(route('teams.destroy', $team));

        $response->assertRedirect(route('teams.index'));

        $this->assertSoftDeleted($team);
    }


    #[Test]
    public function statements_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\TeamController::class,
            'statements',
            \App\Http\Requests\TeamStatementsRequest::class
        );
    }

    #[Test]
    public function statements_saves_and_redirects(): void
    {
        $name = fake()->name();
        $division = Division::factory()->create();
        $max_players = fake()->numberBetween(-10000, 10000);

        $response = $this->get(route('teams.statements'), [
            'name' => $name,
            'division_id' => $division->id,
            'max_players' => $max_players,
        ]);

        $teams = Team::query()
            ->where('name', $name)
            ->where('division_id', $division->id)
            ->where('max_players', $max_players)
            ->get();
        $this->assertCount(1, $teams);
        $team = $teams->first();

        $response->assertRedirect(route('teams.show', ['teams' => $teams]));
        $response->assertSessionHas('team.id', $team->id);
    }
}
