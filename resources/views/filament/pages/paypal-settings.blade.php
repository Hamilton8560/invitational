<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Current Environment Status -->
        <x-filament::section>
            <x-slot name="heading">
                Current PayPal Environment
            </x-slot>

            <x-slot name="description">
                Your active PayPal configuration and sync status
            </x-slot>

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Environment</div>
                            <div class="mt-1 text-lg font-semibold">
                                <span class="inline-flex items-center gap-2">
                                    @if ($this->getCurrentEnvironment() === 'live')
                                        <x-filament::badge color="success">LIVE</x-filament::badge>
                                    @else
                                        <x-filament::badge color="warning">SANDBOX</x-filament::badge>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Client ID</div>
                            <div class="mt-1 text-sm font-mono">
                                {{ $this->hasPayPalConfigured() ? Str::limit($this->getPayPalClientId(), 20) : 'Not configured' }}
                            </div>
                        </div>
                    </div>
                </div>

                @if (!$this->hasPayPalConfigured())
                    <x-filament::section
                        icon="heroicon-o-exclamation-triangle"
                        icon-color="warning"
                    >
                        <x-slot name="heading">
                            PayPal Not Configured
                        </x-slot>

                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <p>Please configure your PayPal keys in your <code>.env</code> file:</p>
                            <ul class="mt-2 list-disc list-inside space-y-1">
                                <li><code>PAYPAL_ENVIRONMENT</code> - Set to 'sandbox' or 'live'</li>
                                <li><code>PAYPAL_SANDBOX_CLIENT_ID</code> & <code>PAYPAL_SANDBOX_CLIENT_SECRET</code> - Your sandbox credentials</li>
                                <li><code>PAYPAL_LIVE_CLIENT_ID</code> & <code>PAYPAL_LIVE_CLIENT_SECRET</code> - Your live credentials</li>
                            </ul>
                        </div>
                    </x-filament::section>
                @endif
            </div>
        </x-filament::section>

        <!-- Sync Status -->
        <x-filament::section>
            <x-slot name="heading">
                Sync Status
            </x-slot>

            <x-slot name="description">
                Items pending synchronization with PayPal
            </x-slot>

            @if ($this->isSyncRunning())
                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                Sync In Progress
                            </p>
                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                Product sync is running in the background. This may take a few minutes. Refresh the page to see updates.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Products Needing Sync</div>
                            <div class="mt-1 text-2xl font-bold">
                                {{ $this->getProductsNeedingSync() }}
                            </div>
                        </div>
                        <x-filament::icon
                            icon="heroicon-o-shopping-bag"
                            class="w-8 h-8 text-gray-400"
                        />
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Packages Needing Sync</div>
                            <div class="mt-1 text-2xl font-bold">
                                {{ $this->getPackagesNeedingSync() }}
                            </div>
                        </div>
                        <x-filament::icon
                            icon="heroicon-o-gift"
                            class="w-8 h-8 text-gray-400"
                        />
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Sync Results -->
        @if ($this->syncResults)
            <x-filament::section
                icon="heroicon-o-check-circle"
                icon-color="success"
            >
                <x-slot name="heading">
                    Last Sync Results
                </x-slot>

                <div class="space-y-3">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <div class="text-xs font-medium text-green-600 dark:text-green-400">Products Created</div>
                            <div class="mt-1 text-lg font-bold text-green-900 dark:text-green-100">
                                {{ $this->syncResults['products_created'] }}
                            </div>
                        </div>

                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="text-xs font-medium text-blue-600 dark:text-blue-400">Products Updated</div>
                            <div class="mt-1 text-lg font-bold text-blue-900 dark:text-blue-100">
                                {{ $this->syncResults['products_updated'] }}
                            </div>
                        </div>

                        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <div class="text-xs font-medium text-green-600 dark:text-green-400">Packages Created</div>
                            <div class="mt-1 text-lg font-bold text-green-900 dark:text-green-100">
                                {{ $this->syncResults['packages_created'] }}
                            </div>
                        </div>

                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="text-xs font-medium text-blue-600 dark:text-blue-400">Packages Updated</div>
                            <div class="mt-1 text-lg font-bold text-blue-900 dark:text-blue-100">
                                {{ $this->syncResults['packages_updated'] }}
                            </div>
                        </div>
                    </div>

                    @if (!empty($this->syncResults['errors']))
                        <div class="mt-4">
                            <div class="text-sm font-medium text-red-600 dark:text-red-400 mb-2">Errors:</div>
                            <ul class="space-y-1">
                                @foreach ($this->syncResults['errors'] as $error)
                                    <li class="text-sm text-red-600 dark:text-red-400">• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </x-filament::section>
        @endif

        <!-- Instructions -->
        <x-filament::section>
            <x-slot name="heading">
                Switching Environments
            </x-slot>

            <x-slot name="description">
                How to switch between sandbox and production PayPal accounts
            </x-slot>

            <div class="prose prose-sm dark:prose-invert max-w-none">
                <ol class="space-y-2">
                    <li>Update <code>PAYPAL_ENVIRONMENT</code> in your <code>.env</code> file to either <code>sandbox</code> or <code>live</code></li>
                    <li>Click the <strong>"Force Re-sync All"</strong> button above to sync all products to the new PayPal environment</li>
                    <li>The system will automatically detect the environment change and create/update all products in your selected PayPal account</li>
                </ol>

                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <p class="text-sm text-blue-900 dark:text-blue-100 mb-2">
                        <strong>Note:</strong> Products remember which PayPal environment they were synced to. When you switch environments,
                        they will automatically be marked as needing re-sync.
                    </p>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
