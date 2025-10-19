<?php

use function Livewire\Volt\{layout};

layout('components.layouts.public');

?>

<div class="min-h-screen bg-zinc-50 dark:bg-zinc-900 py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-8">
            <flux:heading size="xl" class="mb-6">Privacy Policy</flux:heading>

            <div class="prose prose-zinc dark:prose-invert max-w-none">
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-8">
                    <strong>Last Updated:</strong> {{ now()->format('F j, Y') }}
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">1. Introduction</flux:heading>
                <p>
                    The Invitational ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our event registration platform.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">2. Information We Collect</flux:heading>

                <p class="font-semibold mt-6">Personal Information</p>
                <p>
                    When you register for an event, we collect:
                </p>
                <ul>
                    <li>Name and contact information (email, phone number)</li>
                    <li>Date of birth (for age-restricted divisions)</li>
                    <li>Team information (team name, roster details)</li>
                    <li>Event preferences and registration choices</li>
                </ul>

                <p class="font-semibold mt-6">Payment Information</p>
                <p>
                    Payment processing is handled by our payment processor, Paddle.com. We do not store your complete credit card information. Paddle collects and processes payment data in accordance with their privacy policy, available at <a href="https://www.paddle.com/legal/privacy" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400 hover:underline">paddle.com/legal/privacy</a>.
                </p>

                <p class="font-semibold mt-6">Automatically Collected Information</p>
                <p>
                    When you visit our platform, we automatically collect:
                </p>
                <ul>
                    <li>IP address and device information</li>
                    <li>Browser type and version</li>
                    <li>Pages viewed and time spent on pages</li>
                    <li>Referral source and exit pages</li>
                </ul>

                <flux:heading size="lg" class="mt-8 mb-4">3. How We Use Your Information</flux:heading>
                <p>
                    We use the information we collect to:
                </p>
                <ul>
                    <li>Process event registrations and payments</li>
                    <li>Send registration confirmations and event updates</li>
                    <li>Manage team rosters and player invitations</li>
                    <li>Communicate important information about events</li>
                    <li>Improve our platform and user experience</li>
                    <li>Respond to customer service requests</li>
                    <li>Send promotional communications (with your consent)</li>
                    <li>Prevent fraud and ensure platform security</li>
                </ul>

                <flux:heading size="lg" class="mt-8 mb-4">4. Information Sharing and Disclosure</flux:heading>

                <p class="font-semibold mt-6">With Event Organizers</p>
                <p>
                    We share registration information with event organizers to facilitate event management, including team rosters, contact information, and event preferences.
                </p>

                <p class="font-semibold mt-6">With Payment Processors</p>
                <p>
                    Payment information is processed by Paddle.com, our Merchant of Record. Paddle's privacy practices are governed by their Privacy Policy.
                </p>

                <p class="font-semibold mt-6">With Service Providers</p>
                <p>
                    We may share information with third-party service providers who assist us in operating our platform, such as email service providers and hosting services.
                </p>

                <p class="font-semibold mt-6">Legal Requirements</p>
                <p>
                    We may disclose your information when required by law or to protect our rights, property, or safety, or that of our users or others.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">5. Data Security</flux:heading>
                <p>
                    We implement appropriate technical and organizational security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:
                </p>
                <ul>
                    <li>SSL/TLS encryption for data transmission</li>
                    <li>Secure password hashing and authentication</li>
                    <li>Regular security assessments and updates</li>
                    <li>Access controls and data minimization practices</li>
                </ul>
                <p>
                    However, no method of transmission over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">6. Data Retention</flux:heading>
                <p>
                    We retain your personal information for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required or permitted by law. Event registration data is typically retained for:
                </p>
                <ul>
                    <li>Account data: Until you request deletion or close your account</li>
                    <li>Registration records: 7 years for tax and accounting purposes</li>
                    <li>Payment records: As required by Paddle and applicable law</li>
                </ul>

                <flux:heading size="lg" class="mt-8 mb-4">7. Your Rights and Choices</flux:heading>
                <p>
                    You have the following rights regarding your personal information:
                </p>
                <ul>
                    <li><strong>Access:</strong> Request a copy of your personal information</li>
                    <li><strong>Correction:</strong> Request correction of inaccurate information</li>
                    <li><strong>Deletion:</strong> Request deletion of your information (subject to legal obligations)</li>
                    <li><strong>Opt-out:</strong> Unsubscribe from promotional emails via the link in each email</li>
                    <li><strong>Data Portability:</strong> Request your data in a portable format</li>
                </ul>
                <p>
                    To exercise these rights, please contact us through our <a href="{{ route('legal.contact') }}" wire:navigate class="text-blue-600 dark:text-blue-400 hover:underline">Contact</a> page.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">8. Cookies and Tracking Technologies</flux:heading>
                <p>
                    We use cookies and similar tracking technologies to enhance your experience on our platform. You can control cookie preferences through your browser settings, though disabling cookies may affect platform functionality.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">9. Children's Privacy</flux:heading>
                <p>
                    While we offer events for participants of all ages, our platform is not directed to children under 13. We do not knowingly collect personal information from children under 13 without parental consent. If you believe we have collected information from a child under 13, please contact us immediately.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">10. Third-Party Links</flux:heading>
                <p>
                    Our platform may contain links to third-party websites. We are not responsible for the privacy practices of these external sites and encourage you to review their privacy policies.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">11. Changes to This Privacy Policy</flux:heading>
                <p>
                    We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the new Privacy Policy on this page and updating the "Last Updated" date. Your continued use of the platform after changes are posted constitutes acceptance of the updated policy.
                </p>

                <flux:heading size="lg" class="mt-8 mb-4">12. Contact Us</flux:heading>
                <p>
                    If you have questions or concerns about this Privacy Policy or our data practices, please visit our <a href="{{ route('legal.contact') }}" wire:navigate class="text-blue-600 dark:text-blue-400 hover:underline">Contact</a> page.
                </p>
                <p>
                    For payment and billing privacy inquiries, please refer to Paddle's Privacy Policy at <a href="https://www.paddle.com/legal/privacy" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400 hover:underline">paddle.com/legal/privacy</a>.
                </p>
            </div>
        </div>
    </div>
</div>
