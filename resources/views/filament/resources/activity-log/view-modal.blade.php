<div class="space-y-4">
    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Action</h3>
        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->action }}</p>
    </div>

    @if($record->user)
        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">User</h3>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->user->name }} ({{ $record->user->email }})</p>
        </div>
    @endif

    @if($record->event)
        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Event</h3>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->event->name }}</p>
        </div>
    @endif

    @if($record->subject_type && $record->subject_id)
        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Subject</h3>
            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                {{ class_basename($record->subject_type) }} #{{ $record->subject_id }}
            </p>
        </div>
    @endif

    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Timestamp</h3>
        <p class="mt-1 text-sm text-gray-900 dark:text-white">
            {{ $record->created_at->format('M d, Y g:i:s A') }}
            <span class="text-gray-500">({{ $record->created_at->diffForHumans() }})</span>
        </p>
    </div>

    @if($record->ip_address)
        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">IP Address</h3>
            <p class="mt-1 text-sm text-gray-900 dark:text-white font-mono">{{ $record->ip_address }}</p>
        </div>
    @endif

    @if($record->user_agent)
        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">User Agent</h3>
            <p class="mt-1 text-sm text-gray-900 dark:text-white break-all">{{ $record->user_agent }}</p>
        </div>
    @endif

    @if($record->metadata)
        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Metadata</h3>
            <div class="mt-1 bg-gray-100 dark:bg-gray-800 rounded-lg p-3">
                <pre class="text-xs text-gray-900 dark:text-white overflow-auto">{{ json_encode($record->metadata, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    @endif
</div>
