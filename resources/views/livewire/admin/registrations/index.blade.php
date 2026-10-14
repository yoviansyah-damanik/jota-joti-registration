<div>
    <x-ui.page-header :title="__('Registrations')" :subtitle="__('Review and verify troop registration forms')">
        <a href="{{ route('admin.exports.participants') }}" class="btn btn-secondary btn-sm">
            <x-ui.icon name="download" class="size-4" /> {{ __('Export participants') }}
        </a>
    </x-ui.page-header>

    <x-ui.card>
        <div class="grid grid-cols-1 gap-x-4 md:grid-cols-[1fr_200px_200px]">
            <x-ui.input name="search" wire:model.live.debounce.400ms="search" :placeholder="__('Search troop number, name, code, or person in charge...')" />
            <x-ui.select name="status" wire:model.live="status" :options="$statusOptions" :placeholder="__('All statuses')" />
            <x-ui.select name="unit" wire:model.live="unit" :options="$unitOptions" :placeholder="__('All units')" />
        </div>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Troop') }}</th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Person in charge') }}</th>
                        <th class="text-center">{{ __('Participants') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Submitted') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($troops as $troop)
                        <tr wire:key="troop-{{ $troop->id }}" class="hover:bg-base-200">
                            <td>
                                <p class="font-medium">{{ $troop->troop_name }}</p>
                                <p class="text-base-content/60 text-xs">{{ $troop->number_label }} · {{ $troop->unit?->label() }}</p>
                            </td>
                            <td class="font-mono text-sm whitespace-nowrap">{{ $troop->registration_code }}</td>
                            <td>
                                <p>{{ $troop->pic_name }}</p>
                                <p class="text-base-content/60 text-xs">{{ $troop->pic_phone }}</p>
                            </td>
                            <td class="text-center">{{ $troop->participants_count }}</td>
                            <td><x-ui.badge-status :status="$troop->status" /></td>
                            <td class="whitespace-nowrap text-sm">
                                <p>{{ $troop->submitted_at?->translatedFormat('d M Y') }}</p>
                                <p class="text-base-content/60 text-xs">{{ $troop->submitted_at?->format('H:i') }}</p>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('admin.registrations.show', $troop) }}" wire:navigate class="btn btn-primary btn-sm btn-soft">
                                    <x-ui.icon name="eye" class="size-4" /> {{ __('Review') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"><x-ui.empty-state icon="clipboard-check" :title="__('No registrations found')" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $troops->links() }}
    </x-ui.card>
</div>
