<div class="space-y-4">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($statuses as $status)
            <a href="{{ route('admin.registrations.index', ['status' => $status->value]) }}" wire:navigate class="block">
                <x-ui.stat
                    :title="$status->label()"
                    :value="$statusCounts[$status->value] ?? 0"
                    :description="__('troops')"
                    icon="flag"
                    :tone="str_replace('badge-', '', $status->badgeClass())"
                    class="w-full transition hover:shadow-md"
                />
            </a>
        @endforeach
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.stat :title="__('Verified participants')" :value="$totalParticipants" :description="__(':male male · :female female', ['male' => $male, 'female' => $female])" icon="users" />
        <x-ui.stat :title="__('Group number assigned')" :value="$assigned" :description="__(':count not assigned yet', ['count' => $totalParticipants - $assigned])" icon="user-group" tone="secondary" />
        <x-ui.stat :title="__('Assignment progress')" :value="($totalParticipants ? round($assigned / $totalParticipants * 100) : 0).'%'" icon="sparkles" tone="accent" />
    </div>
</div>
