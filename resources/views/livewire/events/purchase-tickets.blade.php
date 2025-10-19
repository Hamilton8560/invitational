<?php

use App\Models\Event;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use function Livewire\Volt\{state, mount, rules, layout, computed};

layout('components.layouts.public');

state(['event', 'product', 'quantity' => 1, 'buyerName', 'buyerEmail', 'buyerPhone', 'termsAccepted' => false]);

mount(function (Event $event, Product $product) {
    $this->event = $event->load(['venue']);
    $this->product = $product;
});

rules([
    'quantity' => 'required|integer|min:1|max:10',
    'buyerName' => 'required|string|max:255',
    'buyerEmail' => 'required|email|max:255',
    'buyerPhone' => 'required|string|max:20',
    'termsAccepted' => 'accepted',
]);

$totalPrice = computed(function () {
    return $this->product->price * $this->quantity;
});

$availableTickets = computed(function () {
    return $this->product->max_quantity - $this->product->current_quantity;
});

$submit = function () {
    $this->validate();

    // Check if enough tickets are available
    if ($this->quantity > $this->availableTickets) {
        $this->addError('quantity', 'Only ' . $this->availableTickets . ' tickets remaining.');
        return;
    }

    DB::transaction(function () {
        // Find or create user
        $user = User::firstOrCreate(
            ['email' => $this->buyerEmail],
            [
                'name' => $this->buyerName,
                'password' => bcrypt(str()->random(32)),
            ]
        );

        // Create sale for payment tracking
        $sale = Sale::create([
            'event_id' => $this->event->id,
            'product_id' => $this->product->id,
            'user_id' => $user->id,
            'quantity' => $this->quantity,
            'unit_price' => $this->product->price,
            'total_amount' => $this->totalPrice,
            'status' => 'pending',
        ]);

        // Increment product quantity by number of tickets sold
        $this->product->increment('current_quantity', $this->quantity);

        // Store sale ID in session for payment flow
        session()->put('pending_sale_id', $sale->id);
    });

    session()->flash('message', 'Ticket purchase submitted successfully! Continue to payment.');

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
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-8 text-center">
                    <flux:icon.lock-closed class="size-16 mx-auto text-zinc-400 mb-4" />
                    <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">
                        Login Required
                    </h2>
                    <p class="text-zinc-600 dark:text-zinc-400 mb-6">
                        Please login or create an account to purchase tickets
                    </p>
                    <div class="flex items-center justify-center gap-4">
                        <flux:button variant="primary" :href="route('login', ['return' => url()->current()])" wire:navigate>
                            Login
                        </flux:button>
                        <flux:button variant="outline" :href="route('register', ['return' => url()->current()])" wire:navigate>
                            Create Account
                        </flux:button>
                    </div>
                </div>
            @else
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Main Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6">
                        <div class="flex items-center gap-3 mb-2">
                            <flux:icon.ticket class="size-6 text-zinc-600 dark:text-zinc-400" />
                            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                                Purchase Spectator Tickets
                            </h1>
                        </div>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-8">
                            Complete the form below to purchase your tickets
                        </p>

                        <form wire:submit="submit" class="space-y-8">
                            <!-- Ticket Selection -->
                            <div>
                                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                                    Ticket Selection
                                </h2>
                                <div class="space-y-4">
                                    <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
                                        <h3 class="font-semibold text-zinc-900 dark:text-white mb-2">{{ $product->name }}</h3>
                                        @if ($product->description)
                                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-3">{{ $product->description }}</p>
                                        @endif
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-zinc-600 dark:text-zinc-400">Price per ticket:</span>
                                            <span class="text-lg font-bold text-zinc-900 dark:text-white">${{ number_format($product->price, 2) }}</span>
                                        </div>
                                    </div>

                                    <flux:field>
                                        <flux:label>Number of Tickets</flux:label>
                                        <flux:input type="number" wire:model.live="quantity" min="1" max="10" />
                                        <flux:error name="quantity" />
                                        <flux:description>{{ $this->availableTickets }} tickets available</flux:description>
                                    </flux:field>

                                    <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4 border border-amber-200 dark:border-amber-800">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-amber-900 dark:text-amber-100">Total Price:</span>
                                            <span class="text-2xl font-bold text-amber-600 dark:text-amber-400">
                                                ${{ number_format($this->totalPrice, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Buyer Information -->
                            <div>
                                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                                    Buyer Information
                                </h2>
                                <div class="space-y-4">
                                    <flux:field>
                                        <flux:label>Full Name</flux:label>
                                        <flux:input wire:model="buyerName" placeholder="John Smith" />
                                        <flux:error name="buyerName" />
                                    </flux:field>

                                    <div class="grid md:grid-cols-2 gap-4">
                                        <flux:field>
                                            <flux:label>Email Address</flux:label>
                                            <flux:input type="email" wire:model="buyerEmail" placeholder="john@example.com" />
                                            <flux:error name="buyerEmail" />
                                        </flux:field>

                                        <flux:field>
                                            <flux:label>Phone Number</flux:label>
                                            <flux:input type="tel" wire:model="buyerPhone" placeholder="(555) 123-4567" />
                                            <flux:error name="buyerPhone" />
                                        </flux:field>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms -->
                            <div>
                                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                                    Terms & Conditions
                                </h2>
                                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6 border border-zinc-200 dark:border-zinc-700">
                                    <div class="prose prose-sm dark:prose-invert max-w-none mb-4">
                                        <p class="text-zinc-600 dark:text-zinc-400">
                                            By purchasing tickets, you acknowledge and agree to:
                                        </p>
                                        <ul class="text-zinc-600 dark:text-zinc-400 list-disc list-inside space-y-1 mt-2">
                                            <li>Tickets are non-transferable and non-refundable</li>
                                            <li>You must bring a valid ID for entry</li>
                                            <li>Event schedule and participants subject to change</li>
                                            <li>Venue rules and regulations must be followed</li>
                                            <li>Refunds available only before the event cutoff date</li>
                                        </ul>
                                    </div>

                                    <flux:field>
                                        <flux:checkbox wire:model="termsAccepted" />
                                        <flux:label>I have read and agree to the terms and conditions</flux:label>
                                        <flux:error name="termsAccepted" />
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

                        <!-- Ticket Details -->
                        <div class="space-y-4 mb-6 pb-6 border-b border-zinc-200 dark:border-zinc-700">
                            <div>
                                <h3 class="text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1">Ticket Type</h3>
                                <p class="text-zinc-900 dark:text-white font-medium">{{ $product->name }}</p>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">Quantity</span>
                                <span class="font-semibold text-zinc-900 dark:text-white">{{ $quantity }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">Price per ticket</span>
                                <span class="font-semibold text-zinc-900 dark:text-white">${{ number_format($product->price, 2) }}</span>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-zinc-900 dark:text-white">
                                <span class="font-medium">Total</span>
                                <span class="text-2xl font-bold">${{ number_format($this->totalPrice, 2) }}</span>
                            </div>

                            <p class="text-xs text-zinc-600 dark:text-zinc-400">
                                Payment processing fee will be calculated at checkout
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endguest
        </div>
</div>
