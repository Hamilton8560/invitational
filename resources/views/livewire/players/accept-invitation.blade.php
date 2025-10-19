<?php

use App\Models\PlayerInvitation;
use App\Models\User;
use App\Models\TeamPlayer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use function Livewire\Volt\{state, mount, rules, layout};

layout('components.layouts.public');

state([
    'invitation',
    'password',
    'password_confirmation',
    'phone',
    'emergency_contact_name',
    'emergency_contact_phone',
]);

mount(function (string $token) {
    $this->invitation = PlayerInvitation::where('token', $token)
        ->with(['team.event', 'team.division'])
        ->firstOrFail();

    // Check if already accepted
    if ($this->invitation->isAccepted()) {
        session()->flash('error', 'This invitation has already been accepted.');
        $this->redirect(route('home'));
        return;
    }

    // Check if expired
    if ($this->invitation->isExpired()) {
        session()->flash('error', 'This invitation has expired. Please contact your team captain.');
        $this->redirect(route('home'));
        return;
    }
});

rules([
    'password' => 'required|string|min:8|confirmed',
    'phone' => 'nullable|string|max:20',
    'emergency_contact_name' => 'required|string|max:255',
    'emergency_contact_phone' => 'required|string|max:20',
]);

$accept = function () {
    $this->validate();

    DB::transaction(function () {
        // Create or update user
        $user = User::updateOrCreate(
            ['email' => $this->invitation->email],
            [
                'name' => $this->invitation->first_name . ' ' . $this->invitation->last_name,
                'password' => Hash::make($this->password),
            ]
        );

        // Create TeamPlayer record
        TeamPlayer::create([
            'team_id' => $this->invitation->team_id,
            'user_id' => $user->id,
            'waiver_signed' => true,
            'waiver_signed_at' => now(),
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
        ]);

        // Update invitation
        $this->invitation->update([
            'user_id' => $user->id,
            'accepted' => true,
            'accepted_at' => now(),
        ]);

        // Increment team player count
        $this->invitation->team->increment('current_players');

        // Log the user in
        Auth::login($user);
    });

    session()->flash('message', 'Welcome to the team! Your registration is complete.');
    $this->redirect(route('dashboard'));
};

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900 flex items-center justify-center py-12">
        <div class="container mx-auto px-4 max-w-2xl">
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <flux:icon.user-plus class="size-16 mx-auto text-amber-600 dark:text-amber-400 mb-4" />
                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">
                        Team Invitation
                    </h1>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        You've been invited to join {{ $invitation->team->name }}
                    </p>
                </div>

                <!-- Event & Team Info -->
                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6 mb-8 border border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Team Details</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">Event:</span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $invitation->team->event->name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">Team:</span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $invitation->team->name }}</span>
                        </div>
                        @if ($invitation->team->division)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">Division:</span>
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $invitation->team->division->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Player Info -->
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-6 mb-8 border border-amber-200 dark:border-amber-800">
                    <h2 class="text-lg font-semibold text-amber-900 dark:text-amber-300 mb-2">Your Information</h2>
                    <p class="text-amber-800 dark:text-amber-200">
                        {{ $invitation->first_name }} {{ $invitation->last_name }}
                    </p>
                    <p class="text-sm text-amber-700 dark:text-amber-300">
                        {{ $invitation->email }}
                    </p>
                </div>

                <!-- Registration Form -->
                <form wire:submit="accept" class="space-y-6">
                    <!-- Account Creation -->
                    <div>
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                            Create Your Account
                        </h2>
                        <div class="space-y-4">
                            <flux:field>
                                <flux:label>Password</flux:label>
                                <flux:input type="password" wire:model="password" placeholder="Choose a secure password" />
                                <flux:error name="password" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Confirm Password</flux:label>
                                <flux:input type="password" wire:model="password_confirmation" placeholder="Confirm your password" />
                                <flux:error name="password_confirmation" />
                            </flux:field>
                        </div>
                    </div>

                    <!-- Emergency Contact -->
                    <div>
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                            Emergency Contact
                        </h2>
                        <div class="space-y-4">
                            <flux:field>
                                <flux:label>Contact Name</flux:label>
                                <flux:input wire:model="emergency_contact_name" placeholder="Emergency contact full name" />
                                <flux:error name="emergency_contact_name" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Contact Phone</flux:label>
                                <flux:input type="tel" wire:model="emergency_contact_phone" placeholder="(555) 123-4567" />
                                <flux:error name="emergency_contact_phone" />
                            </flux:field>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-6 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button type="submit" variant="primary" class="w-full">
                            Accept Invitation & Join Team
                        </flux:button>
                    </div>
                </form>

                <!-- Footer Note -->
                <p class="text-xs text-center text-zinc-600 dark:text-zinc-400 mt-6">
                    By accepting this invitation, you agree to the event terms and conditions and waiver of liability.
                </p>
            </div>
        </div>
</div>
