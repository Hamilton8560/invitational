<?php

use function Livewire\Volt\{layout};

layout('components.layouts.public');

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900 py-6 sm:py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 md:p-8">
            <flux:heading size="xl" class="mb-6">Contact & Support</flux:heading>

            <div class="prose prose-zinc dark:prose-invert max-w-none">
                <p class="text-lg text-zinc-700 dark:text-zinc-300 mb-8">
                    We're here to help! Whether you have questions about registration, need event information, or require technical support, our team is ready to assist you.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">General Support</flux:heading>
                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-lg p-6 mb-6 not-prose">
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <flux:icon.envelope class="size-6 text-zinc-600 dark:text-zinc-400 mt-1 flex-shrink-0" />
                            <div>
                                <div class="font-semibold text-zinc-900 dark:text-white mb-1">Email Support</div>
                                <a href="mailto:support@theinvitational.com" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    support@theinvitational.com
                                </a>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                    Response time: Within 24 hours
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <flux:icon.phone class="size-6 text-zinc-600 dark:text-zinc-400 mt-1 flex-shrink-0" />
                            <div>
                                <div class="font-semibold text-zinc-900 dark:text-white mb-1">Phone Support</div>
                                <a href="tel:+15551234567" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    (555) 123-4567
                                </a>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                    Monday - Friday: 9:00 AM - 5:00 PM EST
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <flux:heading size="lg" class="mt-8 mb-4">Event-Specific Questions</flux:heading>
                <p>
                    For questions about specific events, venues, schedules, or divisions:
                </p>
                <ul>
                    <li>Check the event details page for comprehensive information</li>
                    <li>Email us at <a href="mailto:events@theinvitational.com" class="text-blue-600 dark:text-blue-400 hover:underline">events@theinvitational.com</a></li>
                    <li>Include your event name and registration details in your inquiry</li>
                </ul>

                <flux:heading size="lg" class="mt-8 mb-4">Team & Registration Support</flux:heading>
                <p>
                    Need help with team registrations, roster changes, or player invitations?
                </p>
                <ul>
                    <li>Visit your <a href="{{ route('dashboard') }}" wire:navigate class="text-blue-600 dark:text-blue-400 hover:underline">Dashboard</a> to manage teams</li>
                    <li>Email <a href="mailto:registration@theinvitational.com" class="text-blue-600 dark:text-blue-400 hover:underline">registration@theinvitational.com</a> for assistance</li>
                    <li>Include your team name, event name, and order number</li>
                </ul>

                <flux:heading size="lg" class="mt-8 mb-4">Technical Support</flux:heading>
                <p>
                    Experiencing technical issues with the platform? Email <a href="mailto:tech@theinvitational.com" class="text-blue-600 dark:text-blue-400 hover:underline">tech@theinvitational.com</a> with screenshots and details about your browser and device.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">Frequently Asked Questions</flux:heading>
                <p>
                    Before reaching out, check if your question is answered in our common inquiries:
                </p>
                <ul>
                    <li><a href="{{ route('legal.terms') }}" wire:navigate class="text-blue-600 dark:text-blue-400 hover:underline">Terms of Service</a> - Registration terms and conditions</li>
                    <li><a href="{{ route('legal.refunds') }}" wire:navigate class="text-blue-600 dark:text-blue-400 hover:underline">Refund Policy</a> - Cancellation and refund information</li>
                    <li><a href="{{ route('legal.privacy') }}" wire:navigate class="text-blue-600 dark:text-blue-400 hover:underline">Privacy Policy</a> - How we handle your data</li>
                </ul>

                <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 my-6 not-prose">
                    <p class="text-blue-900 dark:text-blue-200">
                        <strong>Quick Tip:</strong> For the fastest response, include your order number, event name, and a detailed description of your question or issue in your email.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
