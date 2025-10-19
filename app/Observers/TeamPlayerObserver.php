<?php

namespace App\Observers;

use App\Models\TeamPlayer;

class TeamPlayerObserver
{
    /**
     * Handle the TeamPlayer "created" event.
     */
    public function created(TeamPlayer $teamPlayer): void
    {
        // Increment team's current_players count
        $teamPlayer->team->increment('current_players');
    }

    /**
     * Handle the TeamPlayer "updated" event.
     */
    public function updated(TeamPlayer $teamPlayer): void
    {
        //
    }

    /**
     * Handle the TeamPlayer "deleted" event.
     */
    public function deleted(TeamPlayer $teamPlayer): void
    {
        // Decrement team's current_players count
        $teamPlayer->team->decrement('current_players');
    }

    /**
     * Handle the TeamPlayer "restored" event.
     */
    public function restored(TeamPlayer $teamPlayer): void
    {
        // Increment team's current_players count when restored
        $teamPlayer->team->increment('current_players');
    }

    /**
     * Handle the TeamPlayer "force deleted" event.
     */
    public function forceDeleted(TeamPlayer $teamPlayer): void
    {
        //
    }
}
