<div class="space-y-6">
    <x-ui.page-header :title="__('Dashboard')" :subtitle="__('Registration overview')" />

    <livewire:admin.dashboard-stats />

    <x-ui.card :title="__('Waiting for review')" :subtitle="__('Oldest submissions first')" icon="clipboard-check">
        <x-slot:actions>
            <a href="{{ route('admin.registrations.index', ['status' => 'submitted']) }}" wire:navigate class="btn btn-ghost btn-sm">{{ __('View all') }}</a>
        </x-slot:actions>

        @forelse ($latest as $troop)
            <a href="{{ route('admin.registrations.show', $troop) }}" wire:navigate class="hover:bg-base-200 rounded-box -mx-2 flex items-center justify-between gap-4 px-2 py-2">
                <div>
                    <p class="font-medium">{{ $troop->troop_name }}</p>
                    <p class="text-base-content/60 text-xs">{{ $troop->number_label }} · {{ __(':count participants', ['count' => $troop->participants_count]) }}</p>
                </div>
                <span class="text-base-content/60 text-xs">{{ $troop->submitted_at?->diffForHumans() }}</span>
            </a>
        @empty
            <x-ui.empty-state icon="check-circle" :title="__('Nothing to review')" />
        @endforelse
    </x-ui.card>
</div>
