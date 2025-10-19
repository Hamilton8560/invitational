<?php

use App\Models\Event;
use App\Models\Product;
use App\Models\Team;
use App\Models\PlayerInvitation;
use App\Models\Sale;
use App\Models\User;
use App\Notifications\AnonymousNotifiable;
use App\Notifications\PlayerInvitationNotification;
use Illuminate\Support\Facades\DB;
use function Livewire\Volt\{state, mount, rules, layout};

layout('components.layouts.public');

state(['event', 'product', 'teamName', 'captainName', 'captainEmail', 'captainPhone', 'players', 'waiverAccepted' => false]);

mount(function (Event $event, Product $product) {
    $this->event = $event->load(['venue', 'eventTimeSlots']);
    $this->product = $product->load(['division', 'eventTimeSlot']);

    // Initialize players array based on division team size
    $teamSize = $this->product->division?->team_size ?? 5;
    $this->players = collect(range(1, $teamSize))->map(function ($index) {
        return [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'date_of_birth' => '',
        ];
    })->toArray();
});

rules([
    'teamName' => 'required|string|max:255',
    'captainName' => 'required|string|max:255',
    'captainEmail' => 'required|email|max:255',
    'captainPhone' => 'required|string|max:20',
    'players.*.first_name' => 'nullable|string|max:255',
    'players.*.last_name' => 'nullable|string|max:255',
    'players.*.email' => 'nullable|email|max:255',
    'players.*.date_of_birth' => 'nullable|date|before:today',
    'waiverAccepted' => 'accepted',
]);

$submit = function () {
    $this->validate();

    DB::transaction(function () {
        // Find or create captain user
        $captain = User::firstOrCreate(
            ['email' => $this->captainEmail],
            [
                'name' => $this->captainName,
                'password' => bcrypt(str()->random(32)),
            ]
        );

        // Create team
        $team = Team::create([
            'event_id' => $this->event->id,
            'division_id' => $this->product->division_id,
            'owner_id' => $captain->id,
            'name' => $this->teamName,
            'max_players' => $this->product->division?->team_size ?? 5,
            'current_players' => 0,
        ]);

        // Create player invitations and send emails
        // Only process players with complete information
        $validPlayers = collect($this->players)->filter(function ($player) {
            return !empty($player['email']) &&
                   !empty($player['first_name']) &&
                   !empty($player['last_name']);
        });

        foreach ($validPlayers as $playerData) {
            $invitation = PlayerInvitation::create([
                'team_id' => $team->id,
                'first_name' => $playerData['first_name'],
                'last_name' => $playerData['last_name'],
                'email' => $playerData['email'],
                'date_of_birth' => $playerData['date_of_birth'] ?? null,
                'token' => PlayerInvitation::generateToken(),
                'expires_at' => now()->addDays(30),
            ]);

            // Send invitation email
            $notifiable = new AnonymousNotifiable(
                email: $invitation->email,
                name: $invitation->first_name . ' ' . $invitation->last_name
            );
            $notifiable->notify(new PlayerInvitationNotification($invitation));
        }

        // Update team's current player count
        $team->update(['current_players' => $validPlayers->count()]);

        // Create sale for payment tracking
        $sale = Sale::create([
            'event_id' => $this->event->id,
            'product_id' => $this->product->id,
            'team_id' => $team->id,
            'user_id' => $captain->id,
            'quantity' => 1,
            'unit_price' => $this->product->price,
            'total_price' => $this->product->price,
            'status' => 'pending',
        ]);

        // Increment product quantity
        $this->product->increment('current_quantity');

        // Store team ID in session for payment flow
        session()->put('pending_team_id', $team->id);
        session()->put('pending_sale_id', $sale->id);
    });

    session()->flash('message', 'Registration submitted successfully! Continue to payment.');

    // TODO: Redirect to payment page when implemented
    $this->redirect(route('events.show', $this->event->slug));
};

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900">
        <div class="container mx-auto px-4 py-12 max-w-7xl">
            <!-- Back Link -->
            <a href="{{ route('events.show', $event->slug) }}" class="inline-flex items-center gap-2 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white mb-8 transition-colors">
                <flux:icon.arrow-left class="size-4" />
                <span class="text-sm font-medium">Back to Event</span>
            </a>

            @guest
                <!-- Login Required Message -->
                <div class="max-w-2xl mx-auto">
                    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 md:p-8 text-center">
                        <flux:icon.lock-closed class="size-12 md:size-16 mx-auto text-zinc-400 mb-4" />
                        <h2 class="text-xl md:text-2xl font-bold text-zinc-900 dark:text-white mb-2">
                            Login Required
                        </h2>
                        <p class="text-sm md:text-base text-zinc-600 dark:text-zinc-400 mb-6">
                            Please login or create an account to register your team
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
                            <a href="{{ route('login', ['return' => url()->current()]) }}"
                               class="px-6 py-3 text-sm font-medium text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200 rounded-lg transition-colors">
                                Login
                            </a>
                            <a href="{{ route('register', ['return' => url()->current()]) }}"
                               class="px-6 py-3 text-sm font-medium text-zinc-900 dark:text-white bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 rounded-lg transition-colors">
                                Create Account
                            </a>
                        </div>
                    </div>
                </div>
            @else
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Main Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                        <h1 class="text-3xl font-bold text-zinc-900 dark:text-white mb-2">
                            Team Registration
                        </h1>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-8">
                            Complete the form below to register your team
                        </p>

                        <form wire:submit="submit" class="space-y-8">
                            <!-- Team Information -->
                            <div>
                                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                                    Team Information
                                </h2>
                                <div class="space-y-4">
                                    <flux:field>
                                        <flux:label>Team Name</flux:label>
                                        <flux:input wire:model="teamName" placeholder="Enter your team name" />
                                        <flux:error name="teamName" />
                                    </flux:field>
                                </div>
                            </div>

                            <!-- Captain Information -->
                            <div>
                                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                                    Team Captain
                                </h2>
                                <div class="space-y-4">
                                    <flux:field>
                                        <flux:label>Full Name</flux:label>
                                        <flux:input wire:model="captainName" placeholder="John Smith" />
                                        <flux:error name="captainName" />
                                    </flux:field>

                                    <div class="grid sm:grid-cols-2 gap-4">
                                        <flux:field>
                                            <flux:label>Email Address</flux:label>
                                            <flux:input type="email" wire:model="captainEmail" placeholder="john@example.com" />
                                            <flux:error name="captainEmail" />
                                        </flux:field>

                                        <flux:field>
                                            <flux:label>Phone Number</flux:label>
                                            <flux:input type="tel" wire:model="captainPhone" placeholder="(555) 123-4567" />
                                            <flux:error name="captainPhone" />
                                        </flux:field>
                                    </div>
                                </div>
                            </div>

                            <!-- Player Roster -->
                            <div>
                                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                                    Player Roster
                                    <span class="text-sm font-normal text-zinc-600 dark:text-zinc-400 ml-2">
                                        (Optional - Add now or later from your dashboard)
                                    </span>
                                </h2>
                                <flux:text class="mb-4 text-zinc-600 dark:text-zinc-400">
                                    You can register your team now and add players later. Players will receive an email invitation to join your team.
                                </flux:text>

                                <div class="space-y-6">
                                    @foreach ($players as $index => $player)
                                        <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3">
                                                Player {{ $index + 1 }}
                                            </h3>
                                            <div class="grid sm:grid-cols-2 gap-4">
                                                <flux:field>
                                                    <flux:label>First Name</flux:label>
                                                    <flux:input wire:model="players.{{ $index }}.first_name" placeholder="First name" />
                                                    <flux:error name="players.{{ $index }}.first_name" />
                                                </flux:field>

                                                <flux:field>
                                                    <flux:label>Last Name</flux:label>
                                                    <flux:input wire:model="players.{{ $index }}.last_name" placeholder="Last name" />
                                                    <flux:error name="players.{{ $index }}.last_name" />
                                                </flux:field>

                                                <flux:field>
                                                    <flux:label>Email Address</flux:label>
                                                    <flux:input type="email" wire:model="players.{{ $index }}.email" placeholder="player@example.com" />
                                                    <flux:error name="players.{{ $index }}.email" />
                                                </flux:field>

                                                <flux:field>
                                                    <flux:label>Date of Birth</flux:label>
                                                    <flux:input type="date" wire:model="players.{{ $index }}.date_of_birth" />
                                                    <flux:error name="players.{{ $index }}.date_of_birth" />
                                                </flux:field>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Waiver -->
                            <div>
                                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                                    Waiver & Agreement
                                </h2>
                                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6 border border-zinc-200 dark:border-zinc-700">
                                    <div class="prose prose-sm dark:prose-invert max-w-none mb-4">
                                        <p class="text-zinc-600 dark:text-zinc-400">
                                            By registering for this event, you acknowledge that you have read and agree to our terms and conditions, including:
                                        </p>
                                        <ul class="text-zinc-600 dark:text-zinc-400 list-disc list-inside space-y-1 mt-2">
                                            <li>Assumption of risk and waiver of liability</li>
                                            <li>All players must meet division age and skill requirements</li>
                                            <li>Refunds must be requested before the cutoff date</li>
                                            <li>All players must follow event rules and code of conduct</li>
                                        </ul>
                                    </div>

                                    <flux:field>
                                        <flux:checkbox wire:model="waiverAccepted" />
                                        <flux:label>I have read and agree to the terms and conditions</flux:label>
                                        <flux:error name="waiverAccepted" />
                                    </flux:field>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-between pt-6 border-t border-zinc-200 dark:border-zinc-700">
                                <a href="{{ route('events.show', $event->slug) }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                                    Cancel
                                </a>
                                <flux:button type="submit" variant="primary">
                                    Continue to Payment
                                </flux:button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 sticky top-6">
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-6">
                            Order Summary
                        </h2>

                        <!-- Event Details -->
                        <div class="space-y-4 mb-6 pb-6 border-b border-zinc-200 dark:border-zinc-700">
                            <div>
                                <h3 class="text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1">Event</h3>
                                <p class="text-zinc-900 dark:text-white font-medium">{{ $event->name }}</p>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1">Date</h3>
                                <p class="text-zinc-900 dark:text-white">
                                    {{ $event->start_date->format('M j') }} - {{ $event->end_date->format('M j, Y') }}
                                </p>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1">Venue</h3>
                                <p class="text-zinc-900 dark:text-white">{{ $event->venue->name }}</p>
                            </div>
                        </div>

                        <!-- Product Details -->
                        <div class="space-y-4 mb-6 pb-6 border-b border-zinc-200 dark:border-zinc-700">
                            <div>
                                <h3 class="text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1">Sport</h3>
                                <p class="text-zinc-900 dark:text-white font-medium">{{ $product->sport_name }}</p>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1">Division</h3>
                                <p class="text-zinc-900 dark:text-white">
                                    {{ str_replace("Team Registration - ", "", $product->name) }}
                                </p>
                            </div>

                            @if ($product->eventTimeSlot)
                                <div>
                                    <h3 class="text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1">Time Slot</h3>
                                    <p class="text-zinc-900 dark:text-white">
                                        {{ $product->eventTimeSlot->start_time->format('l, g:i A') }}
                                    </p>
                                </div>
                            @endif

                            @if ($product->format)
                                <div>
                                    <h3 class="text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1">Format</h3>
                                    <p class="text-zinc-900 dark:text-white capitalize">
                                        {{ str_replace('_', ' ', $product->format) }}
                                    </p>
                                </div>
                            @endif

                            @if ($product->cash_prize)
                                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 border border-amber-200 dark:border-amber-800">
                                    <h3 class="text-sm font-medium text-amber-800 dark:text-amber-300 mb-1">Cash Prize</h3>
                                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">
                                        ${{ number_format($product->cash_prize, 0) }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Pricing -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-zinc-900 dark:text-white">
                                <span class="font-medium">Entry Fee</span>
                                <span class="text-xl font-bold">${{ number_format($product->price, 2) }}</span>
                            </div>

                            <p class="text-xs text-zinc-600 dark:text-zinc-400">
                                Payment processing fee will be calculated at checkout
                            </p>
                        </div>

                        <!-- Available Spots -->
                        <div class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-600 dark:text-zinc-400">Spots Remaining</span>
                                <span class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $product->spotsRemaining }} / {{ $product->max_quantity }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endguest
        </div>
</div>
