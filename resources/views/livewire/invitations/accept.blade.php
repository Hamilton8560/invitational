<?php

use App\Models\PlayerInvitation;
use App\Models\User;
use App\Models\TeamPlayer;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $token;
    public ?PlayerInvitation $invitation = null;
    public bool $expired = false;
    public bool $alreadyAccepted = false;

    public function mount(string $token): void
    {
        $this->token = $token;

        // Find invitation by token
        $this->invitation = PlayerInvitation::with(['team.product.division', 'team.product.event'])
            ->where('token', $token)
            ->first();

        // Check if invitation exists
        if (!$this->invitation) {
            abort(404, 'Invitation not found');
        }

        // Check if expired
        if ($this->invitation->isExpired()) {
            $this->expired = true;
            return;
        }

        // Check if already accepted
        if ($this->invitation->isAccepted()) {
            $this->alreadyAccepted = true;
            return;
        }

        // Auto-accept invitation
        $this->acceptInvitation();
    }

    public function acceptInvitation(): void
    {
        // Find or create user account
        $user = User::firstOrCreate(
            ['email' => $this->invitation->email],
            [
                'name' => $this->invitation->first_name . ' ' . $this->invitation->last_name,
                'password' => bcrypt(Str::random(32)), // Random password since they'll use magic links
            ]
        );

        // Mark invitation as accepted
        $this->invitation->update([
            'user_id' => $user->id,
            'accepted' => true,
            'accepted_at' => now(),
        ]);

        // Create TeamPlayer record
        TeamPlayer::firstOrCreate(
            [
                'team_id' => $this->invitation->team_id,
                'user_id' => $user->id,
            ],
            [
                'role' => 'player',
            ]
        );

        // Update team's current player count
        $team = $this->invitation->team;
        $team->update([
            'current_players' => $team->playerInvitations()->where('accepted', true)->count(),
        ]);

        // Auto-login the user
        Auth::login($user);

        // Redirect to dashboard with success message
        session()->flash('success', 'Welcome to ' . $team->name . '! You have successfully joined the team.');
        $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<div class="min-h-screen flex items-center justify-center bg-zinc-50 dark:bg-zinc-900 px-4">
    <div class="max-w-md w-full">
        @if($expired)
            <flux:card class="text-center">
                <flux:heading size="lg" class="mb-4">Invitation Expired</flux:heading>
                <flux:text class="mb-6">
                    This invitation link has expired. Please contact your team captain to send you a new invitation.
                </flux:text>
                <flux:button variant="primary" href="{{ route('home') }}" wire:navigate>
                    Return to Home
                </flux:button>
            </flux:card>
        @elseif($alreadyAccepted)
            <flux:card class="text-center">
                <flux:heading size="lg" class="mb-4">Already Accepted</flux:heading>
                <flux:text class="mb-6">
                    You have already accepted this invitation and are a member of {{ $invitation->team->name }}.
                </flux:text>
                <flux:button variant="primary" href="{{ route('dashboard') }}" wire:navigate>
                    Go to Dashboard
                </flux:button>
            </flux:card>
        @else
            <flux:card class="text-center">
                <flux:heading size="lg" class="mb-4">Processing Invitation...</flux:heading>
                <flux:text>
                    Please wait while we set up your account and add you to the team.
                </flux:text>
            </flux:card>
        @endif
    </div>
</div>
