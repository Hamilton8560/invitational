<?php

use App\Models\Sale;
use function Livewire\Volt\{state, mount, layout};

layout('components.layouts.public');

state(['sale']);

mount(function (Sale $sale) {
    $this->sale = $sale;

    // Mark sale as failed and rollback product quantity
    if ($sale->status === 'pending') {
        \DB::transaction(function () use ($sale) {
            $sale->update(['status' => 'failed']);
            $sale->product->decrement('current_quantity', $sale->quantity);
        });
    }
});

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900 py-12">
    <div class="container mx-auto px-4 max-w-2xl">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-8 text-center">
            <div class="w-16 h-16 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>

            <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">
                Payment Cancelled
            </h2>
            <p class="text-zinc-600 dark:text-zinc-400 mb-6">
                Your payment was cancelled. No charges have been made.
            </p>

            <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6 mb-6 text-left">
                <h3 class="font-semibold text-zinc-900 dark:text-white mb-4">Cancelled Order</h3>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-zinc-600 dark:text-zinc-400">
                            @if ($sale->product_id)
                                Product:
                            @elseif ($sale->sponsorship_id)
                                Sponsorship:
                            @else
                                Item:
                            @endif
                        </dt>
                        <dd class="font-medium text-zinc-900 dark:text-white">
                            @if ($sale->product_id && $sale->product)
                                {{ $sale->product->name }}
                            @elseif ($sale->sponsorship_id && $sale->sponsorship)
                                {{ $sale->sponsorship->sponsorPackage->name ?? 'Sponsorship' }}
                            @else
                                Purchase
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-600 dark:text-zinc-400">Amount:</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white">${{ number_format($sale->total_amount, 2) }}</dd>
                    </div>
                    @if ($sale->event)
                        <div class="flex justify-between">
                            <dt class="text-zinc-600 dark:text-zinc-400">Event:</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">{{ $sale->event->name }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="flex gap-4 justify-center">
                @if ($sale->event)
                    <flux:button variant="primary" :href="route('events.show', $sale->event->slug)" wire:navigate>
                        Back to Event
                    </flux:button>
                @endif
                <flux:button variant="outline" :href="route('dashboard')" wire:navigate>
                    Go to Dashboard
                </flux:button>
            </div>
        </div>
    </div>
</div>
