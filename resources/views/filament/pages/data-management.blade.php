<x-filament-panels::page>
    <div class="space-y-6">
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="px-6 py-4">
                <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Excel Data Engine</h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Use the 'Download Templates' button in the top right to generate the required Excel structures. Fill in your corporate data, then use the 'Process Uploads' dropdown to ingest the records.
                </p>
                <div class="mt-4 p-4 rounded-xl bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400 text-sm">
                    <strong>Zero Duplicates Architecture:</strong> The import engine uses constant time (O(1)) composite database upserts. If an identical row is detected, it will be skipped entirely. If a new value is found on an existing record, only that value will be updated safely.
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
