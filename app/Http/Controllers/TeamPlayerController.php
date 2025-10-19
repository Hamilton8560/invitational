<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamPlayerStatementsRequest;
use App\Http\Requests\TeamPlayerStoreRequest;
use App\Http\Requests\TeamPlayerUpdateRequest;
use App\Models\TeamPlayer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamPlayerController extends Controller
{
    public function index(Request $request): View
    {
        $teamPlayers = TeamPlayer::all();

        return view('teamPlayer.index', [
            'teamPlayers' => $teamPlayers,
        ]);
    }

    public function create(Request $request): View
    {
        return view('teamPlayer.create');
    }

    public function store(TeamPlayerStoreRequest $request): RedirectResponse
    {
        $teamPlayer = TeamPlayer::create($request->validated());

        $request->session()->flash('teamPlayer.id', $teamPlayer->id);

        return redirect()->route('teamPlayers.index');
    }

    public function show(Request $request, TeamPlayer $teamPlayer): View
    {
        return view('teamPlayer.show', [
            'teamPlayer' => $teamPlayer,
        ]);
    }

    public function edit(Request $request, TeamPlayer $teamPlayer): View
    {
        return view('teamPlayer.edit', [
            'teamPlayer' => $teamPlayer,
        ]);
    }

    public function update(TeamPlayerUpdateRequest $request, TeamPlayer $teamPlayer): RedirectResponse
    {
        $teamPlayer->update($request->validated());

        $request->session()->flash('teamPlayer.id', $teamPlayer->id);

        return redirect()->route('teamPlayers.index');
    }

    public function destroy(Request $request, TeamPlayer $teamPlayer): RedirectResponse
    {
        $teamPlayer->delete();

        return redirect()->route('teamPlayers.index');
    }

    public function statements(TeamPlayerStatementsRequest $request): RedirectResponse
    {
        $teamPlayer->save();

        $request->session()->flash('teamPlayer.id', $teamPlayer->id);

        return redirect()->route('teams.show', ['team' => $team]);
    }
}
