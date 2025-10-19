<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventStatementsRequest;
use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $events = Event::all();

        return view('event.index', [
            'events' => $events,
        ]);
    }

    public function create(Request $request): View
    {
        return view('event.create');
    }

    public function store(EventStoreRequest $request): RedirectResponse
    {
        $event = Event::create($request->validated());

        $request->session()->flash('event.id', $event->id);

        return redirect()->route('events.index');
    }

    public function show(Request $request, Event $event): View
    {
        return view('event.show', [
            'event' => $event,
        ]);
    }

    public function edit(Request $request, Event $event): View
    {
        return view('event.edit', [
            'event' => $event,
        ]);
    }

    public function update(EventUpdateRequest $request, Event $event): RedirectResponse
    {
        $event->update($request->validated());

        $request->session()->flash('event.id', $event->id);

        return redirect()->route('events.index');
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('events.index');
    }

    public function statements(EventStatementsRequest $request): RedirectResponse
    {
        $event->save();

        $request->session()->flash('event.id', $event->id);

        return redirect()->route('events.index');
    }
}
