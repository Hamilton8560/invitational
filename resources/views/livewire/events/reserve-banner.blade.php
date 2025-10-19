<?php

use App\Models\Event;
use App\Models\Product;
use App\Models\Banner;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use function Livewire\Volt\{state, mount, rules, layout};

layout('components.layouts.public');

state(['event', 'product', 'companyName', 'contactName', 'contactEmail', 'contactPhone', 'bannerLocation', 'waiverAccepted' => false]);

mount(function (Event $event, Product $product) {
    $this->event = $event->load(['venue']);
    $this->product = $product;
});

rules([
    'companyName' => 'required|string|max:255',
    'contactName' => 'required|string|max:255',
    'contactEmail' => 'required|email|max:255',
    'contactPhone' => 'required|string|max:20',
    'bannerLocation' => 'nullable|string|max:255',
    'waiverAccepted' => 'accepted',
]);

$submit = function () {
    $this->validate();

    DB::transaction(function () {
        // Find or create user
        $user = User::firstOrCreate(
            ['email' => $this->contactEmail],
            [
                'name' => $this->contactName,
                'password' => bcrypt(str()->random(32)),
            ]
        );

        // Create banner reservation
        $banner = Banner::create([
            'event_id' => $this->event->id,
            'product_id' => $this->product->id,
            'buyer_id' => $user->id,
            'banner_location' => $this->bannerLocation,
            'company_name' => $this->companyName,
            'contact_name' => $this->contactName,
            'contact_email' => $this->contactEmail,
            'contact_phone' => $this->contactPhone,
        ]);

        // Create sale for payment tracking
        $sale = Sale::create([
            'event_id' => $this->event->id,
            'product_id' => $this->product->id,
            'banner_id' => $banner->id,
            'user_id' => $user->id,
            'quantity' => 1,
            'unit_price' => $this->product->price,
            'total_amount' => $this->product->price,
            'status' => 'pending',
        ]);

        // Increment product quantity
        $this->product->increment('current_quantity');

        // Store banner ID in session for payment flow
        session()->put('pending_banner_id', $banner->id);
        session()->put('pending_sale_id', $sale->id);
    });

    session()->flash('message', 'Banner reservation submitted successfully! Continue to payment.');

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
                        Please login or create an account to reserve a banner advertisement
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
                            <flux:icon.flag class="size-6 text-zinc-600 dark:text-zinc-400" />
                            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                                Reserve Banner Advertisement
                            </h1>
                        </div>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-8">
                            Complete the form below to reserve your banner advertising space
                        </p>

                        <form wire:submit="submit" class="space-y-8">
                            <!-- Company Information -->
                            <div>
                                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                                    Company Information
                                </h2>
                                <div class="space-y-4">
                                    <flux:field>
                                        <flux:label>Company Name</flux:label>
                                        <flux:input wire:model="companyName" placeholder="Enter your company name" />
                                        <flux:error name="companyName" />
                                    </flux:field>

                                    <flux:field>
                                        <flux:label>Preferred Banner Location (Optional)</flux:label>
                                        <flux:select wire:model="bannerLocation">
                                            <option value="">Location will be assigned by event staff</option>
                                            <option value="Main Entrance">Main Entrance</option>
                                            <option value="Court Side">Court Side</option>
                                            <option value="Scoreboard">Scoreboard</option>
                                            <option value="Concession Area">Concession Area</option>
                                            <option value="Other">Other (Staff will contact you)</option>
                                        </flux:select>
                                        <flux:error name="bannerLocation" />
                                        <flux:description>Final location assignment will be confirmed by event staff</flux:description>
                                    </flux:field>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div>
                                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                                    Primary Contact
                                </h2>
                                <div class="space-y-4">
                                    <flux:field>
                                        <flux:label>Full Name</flux:label>
                                        <flux:input wire:model="contactName" placeholder="John Smith" />
                                        <flux:error name="contactName" />
                                    </flux:field>

                                    <div class="grid md:grid-cols-2 gap-4">
                                        <flux:field>
                                            <flux:label>Email Address</flux:label>
                                            <flux:input type="email" wire:model="contactEmail" placeholder="john@company.com" />
                                            <flux:error name="contactEmail" />
                                        </flux:field>

                                        <flux:field>
                                            <flux:label>Phone Number</flux:label>
                                            <flux:input type="tel" wire:model="contactPhone" placeholder="(555) 123-4567" />
                                            <flux:error name="contactPhone" />
                                        </flux:field>
                                    </div>
                                </div>
                            </div>

                            <!-- Waiver -->
                            <div>
                                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                                    Terms & Agreement
                                </h2>
                                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6 border border-zinc-200 dark:border-zinc-700">
                                    <div class="prose prose-sm dark:prose-invert max-w-none mb-4">
                                        <p class="text-zinc-600 dark:text-zinc-400">
                                            By reserving banner advertising space, you acknowledge and agree to:
                                        </p>
                                        <ul class="text-zinc-600 dark:text-zinc-400 list-disc list-inside space-y-1 mt-2">
                                            <li>Provide banner artwork meeting venue specifications</li>
                                            <li>Submit banner design for approval prior to event</li>
                                            <li>Comply with all venue rules and advertising standards</li>
                                            <li>Banner content must be family-friendly and appropriate</li>
                                            <li>Refunds must be requested before the event cutoff date</li>
                                            <li>Final banner placement is at the discretion of event staff</li>
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
                                <h3 class="text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1">Product</h3>
                                <p class="text-zinc-900 dark:text-white font-medium">{{ $product->name }}</p>
                            </div>

                            @if ($product->description)
                                <div>
                                    <h3 class="text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1">Description</h3>
                                    <p class="text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ $product->description }}
                                    </p>
                                </div>
                            @endif

                            @if ($bannerLocation)
                                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-3 border border-zinc-200 dark:border-zinc-700">
                                    <h3 class="text-sm font-medium text-zinc-600 dark:text-zinc-400 mb-1">Preferred Location</h3>
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ $bannerLocation }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Pricing -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-zinc-900 dark:text-white">
                                <span class="font-medium">Banner Fee</span>
                                <span class="text-xl font-bold">${{ number_format($product->price, 2) }}</span>
                            </div>

                            <p class="text-xs text-zinc-600 dark:text-zinc-400">
                                Payment processing fee will be calculated at checkout
                            </p>
                        </div>

                        <!-- Available Spots -->
                        <div class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-600 dark:text-zinc-400">Banners Remaining</span>
                                <span class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $product->max_quantity - $event->banners()->count() }} / {{ $product->max_quantity }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endguest
        </div>
</div>
