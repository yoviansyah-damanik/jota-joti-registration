<div class="space-y-3">
    @if ($troopId && $session->is_active)
        <div class="flex justify-end">
            <button type="button" class="btn btn-success btn-soft btn-sm" wire:click="markTroopPresent('{{ $troopId }}')"
                x-confirm="{ message: @js(__('Mark all unrecorded participants of this troop as present?')), tone: 'success', confirm: @js(__('Mark all present')) }">
                <x-ui.icon name="check-circle" class="size-4" /> {{ __('Mark all present for this troop') }}
            </button>
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-2 text-sm">
        <span class="text-base-content/60">{{ __('Present') }}:</span>
        <span class="badge badge-soft badge-info">{{ __('Male') }} {{ $presentByGender['male'] }}</span>
        <span class="badge badge-soft badge-secondary">{{ __('Female') }} {{ $presentByGender['female'] }}</span>
    </div>

    <div class="overflow-x-auto">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>{{ __('Participant') }}</th>
                    <th>{{ __('Troop') }}</th>
                    <th class="text-center">{{ __('Group') }}</th>
                    <th>{{ __('Check-in') }}</th>
                    <th class="text-right">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($participants as $participant)
                    @php $record = $participant->attendances->first(); @endphp
                    <tr wire:key="att-{{ $participant->id }}">
                        <td>
                            <p class="font-medium">{{ $participant->full_name }}</p>
                            <p class="text-base-content/60 font-mono text-xs">{{ $participant->attendance_code ?? $participant->kta_number }} · {{ $participant->gender->value }}</p>
                        </td>
                        <td class="text-sm">
                            <p>{{ $participant->troop->number_label }}</p>
                            <p class="text-base-content/60 max-w-48 truncate text-xs">{{ $participant->troop->troop_name }}</p>
                        </td>
                        <td class="text-center">
                            <x-ui.group-badge :number="$participant->group_number" />
                        </td>
                        <td class="text-sm">{{ $record?->checked_in_at?->format('H:i') ?? '-' }}</td>
                        <td class="text-right">
                            <div class="join">
                                @foreach ($statuses as $status)
                                    <button type="button"
                                        wire:click="setStatus('{{ $participant->id }}', '{{ $status->value }}')"
                                        @disabled(! $session->is_active)
                                        @class([
                                            'btn btn-xs join-item',
                                            str_replace('badge-', 'btn-', $status->badgeClass()) => $record?->status === $status,
                                        ])>
                                        {{ $status->label() }}
                                    </button>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-ui.empty-state icon="users" :title="__('No participants found')" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $participants->links() }}
</div>
