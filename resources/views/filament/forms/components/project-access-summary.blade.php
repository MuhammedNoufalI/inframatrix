@php
    $project = $getRecord();
    if (! $project) {
        echo '<p class="text-gray-500 dark:text-gray-400 text-sm">Save the project first to view the access summary.</p>';
        return;
    }

    $admins = \App\Models\User::role('admin')->get();
    $infraAdmins = \App\Models\User::role('infra_admin')->get();
    $assignedUsers = $project->users()->withPivot('role')->get();
@endphp

<div class="fi-ta-ctn rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <table class="fi-ta-table w-full text-left divide-y divide-gray-200 dark:divide-white/5 text-sm">
        <thead class="bg-gray-50 dark:bg-white/5">
            <tr>
                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 font-semibold text-gray-950 dark:text-white">User</th>
                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 font-semibold text-gray-950 dark:text-white">System Role</th>
                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 font-semibold text-gray-950 dark:text-white">Project Role</th>
                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 font-semibold text-gray-950 dark:text-white">Access Level</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-white/5 whitespace-nowrap">
            @foreach($admins as $admin)
                <tr class="fi-ta-row transition duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3"><div class="fi-ta-col-wrp px-3 py-4 text-gray-950 dark:text-white">{{ $admin->name }} ({{ $admin->email }})</div></td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3"><div class="fi-ta-col-wrp px-3 py-4 text-primary-600 dark:text-primary-400 font-medium">Admin</div></td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3"><div class="fi-ta-col-wrp px-3 py-4 text-gray-500 dark:text-gray-400">Global</div></td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <div class="fi-ta-col-wrp px-3 py-4">
                            <span class="inline-flex items-center justify-center min-h-6 px-2 py-0.5 text-xs font-medium tracking-tight rounded-xl bg-success-100 text-success-700 dark:bg-success-500/10 dark:text-success-400 ring-1 ring-inset ring-success-600/20 dark:ring-success-400/30">
                                Full Global Access
                            </span>
                        </div>
                    </td>
                </tr>
            @endforeach

            @foreach($infraAdmins as $infraAdmin)
                <tr class="fi-ta-row transition duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3"><div class="fi-ta-col-wrp px-3 py-4 text-gray-950 dark:text-white">{{ $infraAdmin->name }} ({{ $infraAdmin->email }})</div></td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3"><div class="fi-ta-col-wrp px-3 py-4 text-primary-600 dark:text-primary-400 font-medium">Infra Admin</div></td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3"><div class="fi-ta-col-wrp px-3 py-4 text-gray-500 dark:text-gray-400">Global</div></td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <div class="fi-ta-col-wrp px-3 py-4">
                            <span class="inline-flex items-center justify-center min-h-6 px-2 py-0.5 text-xs font-medium tracking-tight rounded-xl bg-success-100 text-success-700 dark:bg-success-500/10 dark:text-success-400 ring-1 ring-inset ring-success-600/20 dark:ring-success-400/30">
                                Full Project Access
                            </span>
                        </div>
                    </td>
                </tr>
            @endforeach

            @foreach($assignedUsers as $user)
                @php
                    $isGlobal = $user->hasRole(['admin', 'infra_admin']);
                    if($isGlobal) continue; // Already shown above
                @endphp
                <tr class="fi-ta-row transition duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3"><div class="fi-ta-col-wrp px-3 py-4 text-gray-950 dark:text-white">{{ $user->name }} ({{ $user->email }})</div></td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3"><div class="fi-ta-col-wrp px-3 py-4 text-gray-500 dark:text-gray-400">Viewer (Default)</div></td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3"><div class="fi-ta-col-wrp px-3 py-4 text-gray-950 dark:text-white capitalize">{{ $user->pivot->role }}</div></td>
                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        <div class="fi-ta-col-wrp px-3 py-4">
                            @if($user->pivot->role === 'viewer')
                                <span class="inline-flex items-center justify-center min-h-6 px-2 py-0.5 text-xs font-medium tracking-tight rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400 ring-1 ring-inset ring-gray-600/20 dark:ring-gray-400/30">
                                    View Only
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center min-h-6 px-2 py-0.5 text-xs font-medium tracking-tight rounded-xl bg-warning-100 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400 ring-1 ring-inset ring-warning-600/20 dark:ring-warning-400/30">
                                    Edit Access
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
