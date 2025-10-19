<?php

use function Livewire\Volt\{layout};

layout('components.layouts.app');

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900 py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-8">
            <flux:heading size="xl" class="mb-6">Terms of Service</flux:heading>

            <div class="prose prose-zinc dark:prose-invert max-w-none">
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-8">
                    <strong>Last Updated:</strong> {{ now()->format('F j, Y') }}
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">1. Acceptance of Terms</flux:heading>
                <p>
                    By accessing and using The Invitational's event registration platform (the "Service"), you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to these Terms of Service, please do not use the Service.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">2. Merchant of Record</flux:heading>
                <div class="bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-500 p-4 my-6">
                    <p class="font-semibold text-amber-900 dark:text-amber-200">
                        Our order process is conducted by our online reseller Paddle.com. Paddle.com is the Merchant of Record for all our orders. Paddle provides all customer service inquiries and handles returns.
                    </p>
                </div>
                <p>
                    For payment-related inquiries, please contact Paddle directly at <a href="https://www.paddle.net" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400 hover:underline">paddle.net</a>.
                </p>
                <p>
                    Paddle's Checkout Buyer Terms apply to all transactions and can be found at <a href="https://www.paddle.com/legal/checkout-buyer-terms" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400 hover:underline">paddle.com/legal/checkout-buyer-terms</a>.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">3. Event Registration</flux:heading>
                <p>
                    When you register for an event through our platform, you agree to:
                </p>
                <ul>
                    <li>Provide accurate and complete registration information</li>
                    <li>Maintain the security of your account credentials</li>
                    <li>Accept responsibility for all activities under your account</li>
                    <li>Notify us immediately of any unauthorized use of your account</li>
                </ul>

                <flux:heading size="lg" class="mt-8 mb-4">4. Team Registrations</flux:heading>
                <p>
                    Team captains are responsible for:
                </p>
                <ul>
                    <li>Ensuring all team members meet age and skill requirements for their division</li>
                    <li>Maintaining accurate roster information</li>
                    <li>Communicating event details to all team members</li>
                    <li>Ensuring team members accept their invitation to join the team</li>
                </ul>

                <flux:heading size="lg" class="mt-8 mb-4">5. Payment Terms</flux:heading>
                <p>
                    All fees are due at the time of registration. Registration is not complete until payment is received and confirmed by Paddle. We reserve the right to cancel registrations for non-payment.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">6. Cancellations and Refunds</flux:heading>
                <p>
                    Please refer to our <a href="{{ route('legal.refunds') }}" wire:navigate class="text-blue-600 dark:text-blue-400 hover:underline">Refund Policy</a> for detailed information about cancellations and refunds.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">7. Event Changes</flux:heading>
                <p>
                    We reserve the right to:
                </p>
                <ul>
                    <li>Modify event schedules, venues, or formats as necessary</li>
                    <li>Cancel events due to insufficient registration or unforeseen circumstances</li>
                    <li>Combine or split divisions based on registration numbers</li>
                </ul>
                <p>
                    Registered participants will be notified of any significant changes via email.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">8. Code of Conduct</flux:heading>
                <p>
                    All participants agree to:
                </p>
                <ul>
                    <li>Conduct themselves in a sportsmanlike manner</li>
                    <li>Respect officials, opponents, and venue staff</li>
                    <li>Follow all venue rules and regulations</li>
                    <li>Refrain from abusive, threatening, or discriminatory behavior</li>
                </ul>
                <p>
                    Violations may result in ejection from the event without refund.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">9. Liability Waiver</flux:heading>
                <p>
                    By registering for an event, you acknowledge that participation in sports activities carries inherent risks. You agree to:
                </p>
                <ul>
                    <li>Participate at your own risk</li>
                    <li>Release The Invitational, its organizers, sponsors, and venue operators from liability for injuries or damages</li>
                    <li>Carry your own health insurance coverage</li>
                    <li>Ensure you are physically fit to participate</li>
                </ul>

                <flux:heading size="lg" class="mt-8 mb-4">10. Privacy</flux:heading>
                <p>
                    Your privacy is important to us. Please review our <a href="{{ route('legal.privacy') }}" wire:navigate class="text-blue-600 dark:text-blue-400 hover:underline">Privacy Policy</a> to understand how we collect, use, and protect your personal information.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">11. Intellectual Property</flux:heading>
                <p>
                    All content on this platform, including text, graphics, logos, and software, is the property of The Invitational and is protected by copyright and trademark laws.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">12. Modifications to Terms</flux:heading>
                <p>
                    We reserve the right to modify these Terms of Service at any time. Changes will be effective immediately upon posting. Your continued use of the Service after changes are posted constitutes acceptance of the modified terms.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">13. Contact Information</flux:heading>
                <p>
                    For questions about these Terms of Service, please visit our <a href="{{ route('legal.contact') }}" wire:navigate class="text-blue-600 dark:text-blue-400 hover:underline">Contact</a> page.
                </p>
                <p>
                    For payment and billing inquiries, please contact Paddle at <a href="https://www.paddle.net" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400 hover:underline">paddle.net</a>.
                </p>
            </div>
        </div>
    </div>
</div>
