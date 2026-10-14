<x-ui.card :title="__('Fee details')" :subtitle="__(':count troops', ['count' => $troops->total()])" icon="table">
    <x-slot:actions>
        <input type="search" wire:model.live.debounce.300ms="search" class="input input-sm w-64" placeholder="{{ __('Search troop number or name...') }}" />
    </x-slot:actions>

    <div class="rounded-box overflow-x-auto">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Troop') }}</th>
                    <th class="text-right">{{ __('Participants') }}</th>
                    <th class="text-right">{{ __('Fee per participant') }}</th>
                    <th class="text-right">{{ __('Amount') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Proof of Payment') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($troops as $troop)
                    @php
                        $date = $troop->verified_at ?? $troop->submitted_at;
                        $proof = $troop->documents->first();
                    @endphp
                    <tr wire:key="pay-{{ $troop->id }}">
                        <td class="whitespace-nowrap">
                            {{ $date?->translatedFormat('d M Y') }}
                            <span class="text-base-content/50 block text-xs">{{ $troop->verified_at ? __('Verified') : __('Submitted') }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.registrations.show', $troop) }}" wire:navigate class="link link-hover font-medium">{{ $troop->troop_name }}</a>
                            <span class="text-base-content/60 block font-mono text-xs">{{ $troop->number_label }}</span>
                        </td>
                        <td class="text-right tabular-nums">{{ $troop->participants_count }}</td>
                        <td class="whitespace-nowrap text-right tabular-nums">{{ \App\Support\Rupiah::format($troop->fee_per_participant) }}</td>
                        <td class="whitespace-nowrap text-right font-semibold tabular-nums">{{ \App\Support\Rupiah::format($troop->fee_total) }}</td>
                        <td><x-ui.badge-status :status="$troop->status" /></td>
                        <td>
                            @if ($proof)
                                <a href="{{ route('admin.documents.show', [$proof, 'inline' => 1]) }}" target="_blank" class="link link-primary text-sm">{{ __('View') }}</a>
                            @else
                                <span class="text-base-content/40">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-ui.empty-state icon="banknotes" :title="__('No fee data for this filter')" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $troops->links() }}
</x-ui.card>
