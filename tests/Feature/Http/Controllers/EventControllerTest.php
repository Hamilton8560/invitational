<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\EventController
 */
final class EventControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        $events = Event::factory()->count(3)->create();

        $response = $this->get(route('events.index'));

        $response->assertOk();
        $response->assertViewIs('event.index');
        $response->assertViewHas('events');
    }


    #[Test]
    public function create_displays_view(): void
    {
        $response = $this->get(route('events.create'));

        $response->assertOk();
        $response->assertViewIs('event.create');
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\EventController::class,
            'store',
            \App\Http\Requests\EventStoreRequest::class
        );
    }

    #[Test]
    public function store_saves_and_redirects(): void
    {
        $venue = Venue::factory()->create();
        $name = fake()->name();
        $start_date = Carbon::parse(fake()->date());
        $end_date = Carbon::parse(fake()->date());
        $status = fake()->randomElement(/** enum_attributes **/);
        $refund_cutoff_date = Carbon::parse(fake()->date());

        $response = $this->post(route('events.store'), [
            'venue_id' => $venue->id,
            'name' => $name,
            'start_date' => $start_date->toDateString(),
            'end_date' => $end_date->toDateString(),
            'status' => $status,
            'refund_cutoff_date' => $refund_cutoff_date->toDateString(),
        ]);

        $events = Event::query()
            ->where('venue_id', $venue->id)
            ->where('name', $name)
            ->where('start_date', $start_date)
            ->where('end_date', $end_date)
            ->where('status', $status)
            ->where('refund_cutoff_date', $refund_cutoff_date)
            ->get();
        $this->assertCount(1, $events);
        $event = $events->first();

        $response->assertRedirect(route('events.index'));
        $response->assertSessionHas('event.id', $event->id);
    }


    #[Test]
    public function show_displays_view(): void
    {
        $event = Event::factory()->create();

        $response = $this->get(route('events.show', $event));

        $response->assertOk();
        $response->assertViewIs('event.show');
        $response->assertViewHas('event');
    }


    #[Test]
    public function edit_displays_view(): void
    {
        $event = Event::factory()->create();

        $response = $this->get(route('events.edit', $event));

        $response->assertOk();
        $response->assertViewIs('event.edit');
        $response->assertViewHas('event');
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\EventController::class,
            'update',
            \App\Http\Requests\EventUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $event = Event::factory()->create();
        $venue = Venue::factory()->create();
        $name = fake()->name();
        $start_date = Carbon::parse(fake()->date());
        $end_date = Carbon::parse(fake()->date());
        $status = fake()->randomElement(/** enum_attributes **/);
        $refund_cutoff_date = Carbon::parse(fake()->date());

        $response = $this->put(route('events.update', $event), [
            'venue_id' => $venue->id,
            'name' => $name,
            'start_date' => $start_date->toDateString(),
            'end_date' => $end_date->toDateString(),
            'status' => $status,
            'refund_cutoff_date' => $refund_cutoff_date->toDateString(),
        ]);

        $event->refresh();

        $response->assertRedirect(route('events.index'));
        $response->assertSessionHas('event.id', $event->id);

        $this->assertEquals($venue->id, $event->venue_id);
        $this->assertEquals($name, $event->name);
        $this->assertEquals($start_date, $event->start_date);
        $this->assertEquals($end_date, $event->end_date);
        $this->assertEquals($status, $event->status);
        $this->assertEquals($refund_cutoff_date, $event->refund_cutoff_date);
    }


    #[Test]
    public function destroy_deletes_and_redirects(): void
    {
        $event = Event::factory()->create();

        $response = $this->delete(route('events.destroy', $event));

        $response->assertRedirect(route('events.index'));

        $this->assertSoftDeleted($event);
    }


    #[Test]
    public function statements_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\EventController::class,
            'statements',
            \App\Http\Requests\EventStatementsRequest::class
        );
    }

    #[Test]
    public function statements_saves_and_redirects(): void
    {
        $name = fake()->name();
        $start_date = Carbon::parse(fake()->date());
        $end_date = Carbon::parse(fake()->date());
        $venue = Venue::factory()->create();

        $response = $this->get(route('events.statements'), [
            'name' => $name,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'venue_id' => $venue->id,
        ]);

        $events = Event::query()
            ->where('name', $name)
            ->where('start_date', $start_date)
            ->where('end_date', $end_date)
            ->where('venue_id', $venue->id)
            ->get();
        $this->assertCount(1, $events);
        $event = $events->first();

        $response->assertRedirect(route('events.index'));
        $response->assertSessionHas('event.id', $event->id);
    }
}
