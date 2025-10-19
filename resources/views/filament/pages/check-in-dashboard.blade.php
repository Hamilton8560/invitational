<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Event Selector --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <form wire:submit.prevent>
                {{ $this->form }}
            </form>
        </div>

        @if($eventId)
            {{-- Stats Grid --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->getStats() as $stat)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    {{ $stat['label'] }}
                                </p>
                                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                                    {{ $stat['value'] }}
                                </p>
                            </div>
                            <div class="p-3 bg-{{ $stat['color'] }}-100 dark:bg-{{ $stat['color'] }}-900 rounded-full">
                                <x-filament::icon
                                    :icon="$stat['icon']"
                                    class="w-6 h-6 text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400"
                                />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Recent Check-Ins --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Recent Check-Ins
                    </h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($this->getRecentCheckins() as $checkin)
                        <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ substr($checkin->user->name, 0, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ $checkin->user->name }}
                                            </h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $checkin->user->email }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($checkin->check_in_type === 'team') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                            @elseif($checkin->check_in_type === 'individual') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                            @elseif($checkin->check_in_type === 'vendor') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                            @endif">
                                            {{ ucfirst($checkin->check_in_type) }}
                                        </span>
                                        @if($checkin->team)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                {{ $checkin->team->name }}
                                            </span>
                                        @endif
                                        @if($checkin->individualPlayer)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                                                {{ $checkin->individualPlayer->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="ml-4 flex flex-col items-end gap-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $checkin->checked_in_at->format('g:i A') }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $checkin->checked_in_at->diffForHumans() }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        By: {{ $checkin->checkedInBy->name }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                No check-ins yet
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
                <p class="text-gray-500 dark:text-gray-400">
                    Please select an event to view check-in data
                </p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
