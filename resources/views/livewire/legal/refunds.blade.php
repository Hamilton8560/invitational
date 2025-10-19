<?php

use function Livewire\Volt\{layout};

layout('components.layouts.public');

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900 py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-8">
            <flux:heading size="xl" class="mb-6">Refund Policy</flux:heading>

            <div class="prose prose-zinc dark:prose-invert max-w-none">
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-8">
                    <strong>Last Updated:</strong> {{ now()->format('F j, Y') }}
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">1. Overview</flux:heading>
                <p>
                    At The Invitational, we strive to provide exceptional event experiences. We understand that circumstances change, and we've designed our refund policy to be fair and transparent for all participants.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">2. 30-Day Money-Back Guarantee</flux:heading>
                <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 my-6">
                    <p class="font-semibold text-green-900 dark:text-green-200">
                        We offer a 30-day money-back guarantee for all event registrations. If you're not satisfied with your registration for any reason, you may request a full refund within 30 days of purchase.
                    </p>
                </div>

                <flux:heading size="lg" class="mt-8 mb-4">3. Refund Eligibility</flux:heading>

                <p class="font-semibold mt-6">Full Refunds (100%)</p>
                <p>
                    You are eligible for a full refund in the following circumstances:
                </p>
                <ul>
                    <li>Cancellation within 30 days of registration</li>
                    <li>Event is cancelled by The Invitational</li>
                    <li>Event is significantly modified (date, location, or format changes)</li>
                    <li>You are unable to participate due to documented medical reasons</li>
                    <li>Duplicate registrations or billing errors</li>
                </ul>

                <p class="font-semibold mt-6">Partial Refunds (50%)</p>
                <p>
                    Partial refunds may be issued in the following circumstances:
                </p>
                <ul>
                    <li>Cancellation between 30-60 days before the event date</li>
                    <li>Transfer to another participant is not possible</li>
                </ul>

                <p class="font-semibold mt-6">No Refunds</p>
                <p>
                    Refunds will not be issued in the following circumstances:
                </p>
                <ul>
                    <li>Cancellation within 14 days of the event date</li>
                    <li>No-shows or failure to attend without prior notice</li>
                    <li>Disqualification due to Code of Conduct violations</li>
                    <li>Weather-related delays or minor schedule adjustments</li>
                </ul>

                <flux:heading size="lg" class="mt-8 mb-4">4. Team Registration Refunds</flux:heading>
                <p>
                    For team registrations, the refund policy applies to the entire team registration fee. Individual team members cannot receive partial refunds. However, team captains may:
                </p>
                <ul>
                    <li>Replace team members at no additional cost up to 7 days before the event</li>
                    <li>Request a full team refund following the standard refund timeline</li>
                    <li>Transfer the registration to another team captain with approval</li>
                </ul>

                <flux:heading size="lg" class="mt-8 mb-4">5. Spectator Tickets, Booths, and Sponsorships</flux:heading>

                <p class="font-semibold mt-6">Spectator Tickets</p>
                <ul>
                    <li>Full refund if cancelled 48+ hours before the event</li>
                    <li>No refund for cancellations within 48 hours of the event</li>
                </ul>

                <p class="font-semibold mt-6">Vendor Booths and Banner Advertising</p>
                <ul>
                    <li>Full refund if cancelled 60+ days before the event</li>
                    <li>50% refund if cancelled 30-60 days before the event</li>
                    <li>No refund for cancellations within 30 days of the event</li>
                </ul>

                <flux:heading size="lg" class="mt-8 mb-4">6. How to Request a Refund</flux:heading>
                <p>
                    To request a refund:
                </p>
                <ol>
                    <li>Contact us through our <a href="{{ route('legal.contact') }}" wire:navigate class="text-blue-600 dark:text-blue-400 hover:underline">Contact</a> page</li>
                    <li>Provide your registration details (order number, event name, registration email)</li>
                    <li>Explain the reason for your refund request</li>
                    <li>Include any supporting documentation (medical notes, transfer requests, etc.)</li>
                </ol>
                <p class="mt-4">
                    For payment and billing inquiries, you may also contact Paddle directly at <a href="https://www.paddle.net" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400 hover:underline">paddle.net</a>.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">7. Refund Processing Time</flux:heading>
                <p>
                    Once your refund request is approved:
                </p>
                <ul>
                    <li>Refunds are processed by Paddle.com, our payment processor</li>
                    <li>Processing typically takes 5-10 business days</li>
                    <li>Refunds will be issued to the original payment method</li>
                    <li>You will receive an email confirmation when the refund is processed</li>
                </ul>

                <flux:heading size="lg" class="mt-8 mb-4">8. Registration Transfers</flux:heading>
                <p>
                    As an alternative to refunds, we offer free registration transfers:
                </p>
                <ul>
                    <li>Transfer your registration to another participant at no cost</li>
                    <li>Transfer to a future event (subject to availability and price differences)</li>
                    <li>Transfers must be requested at least 7 days before the event</li>
                </ul>
                <p>
                    To request a transfer, contact us through our <a href="{{ route('legal.contact') }}" wire:navigate class="text-blue-600 dark:text-blue-400 hover:underline">Contact</a> page.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">9. Event Cancellations by The Invitational</flux:heading>
                <p>
                    If we must cancel an event due to unforeseen circumstances:
                </p>
                <ul>
                    <li>All registered participants will receive a full refund automatically</li>
                    <li>Refunds will be processed within 14 business days of cancellation</li>
                    <li>We will make every effort to reschedule and offer participants priority registration</li>
                    <li>Participants will be notified via email and phone (if provided)</li>
                </ul>

                <flux:heading size="lg" class="mt-8 mb-4">10. Weather and Force Majeure</flux:heading>
                <p>
                    Events may be delayed or modified due to weather or other circumstances beyond our control:
                </p>
                <ul>
                    <li>Minor delays or schedule adjustments do not qualify for refunds</li>
                    <li>If an event is postponed, registrations will transfer to the new date</li>
                    <li>If you cannot attend the rescheduled date, a full refund will be issued</li>
                </ul>

                <flux:heading size="lg" class="mt-8 mb-4">11. Chargebacks</flux:heading>
                <p>
                    If you initiate a chargeback with your bank or credit card company without first contacting us:
                </p>
                <ul>
                    <li>Your registration may be cancelled</li>
                    <li>You may be prohibited from registering for future events</li>
                    <li>Please contact us first to resolve any billing issues</li>
                </ul>

                <flux:heading size="lg" class="mt-8 mb-4">12. Changes to This Policy</flux:heading>
                <p>
                    We reserve the right to modify this Refund Policy at any time. Changes will be effective immediately upon posting. Your registration under the current policy will be honored according to the terms in effect at the time of registration.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">13. Contact Us</flux:heading>
                <p>
                    If you have questions about our Refund Policy or need to request a refund, please visit our <a href="{{ route('legal.contact') }}" wire:navigate class="text-blue-600 dark:text-blue-400 hover:underline">Contact</a> page.
                </p>
                <p>
                    For payment processing questions, contact Paddle at <a href="https://www.paddle.net" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400 hover:underline">paddle.net</a>.
                </p>
            </div>
        </div>
    </div>
</div>
