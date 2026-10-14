<x-ui.card :title="__('Participants')" :subtitle="__(':total participants · :male male · :female female', ['total' => $participants->count(), 'male' => $genderCounts['L'] ?? 0, 'female' => $genderCounts['P'] ?? 0])" icon="users">
    <x-slot:actions>
        <div class="flex flex-wrap items-center gap-2">
            <input type="search" wire:model.live.debounce.300ms="search" class="input input-sm w-48 sm:w-56" placeholder="{{ __('Search KTA or name...') }}" />
            <button type="button" class="btn btn-primary btn-sm" wire:click="openAddModal">
                <x-ui.icon name="plus" class="size-4" /> {{ __('Add Participant') }}
            </button>
        </div>
    </x-slot:actions>

    <div class="rounded-box max-h-[32rem] overflow-auto">
        <table class="table table-sm table-pin-rows">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('KTA number') }}</th>
                    <th>{{ __('Participant ID') }}</th>
                    <th>{{ __('Full name') }}</th>
                    <th>{{ __('Date of birth') }}</th>
                    <th>{{ __('Age') }}</th>
                    <th>{{ __('Gender') }}</th>
                    <th>{{ __('Group') }}</th>
                    <th class="text-right">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($participants as $participant)
                    <tr wire:key="participant-{{ $participant->id }}">
                        <td class="text-base-content/50">{{ $loop->iteration }}</td>
                        <td class="font-mono">{{ $participant->kta_number ?: '-' }}</td>
                        <td class="whitespace-nowrap font-mono text-xs">{{ $participant->attendance_code ?? '-' }}</td>
                        <td class="font-semibold">{{ $participant->full_name }}</td>
                        <td>{{ $participant->birth_date->translatedFormat('d M Y') }}</td>
                        <td>{{ (int) $participant->birth_date->diffInYears(now()) }}</td>
                        <td>{{ $participant->gender->label() }}</td>
                        <td>
                            <x-ui.group-badge :number="$participant->group_number" />
                        </td>
                        <td class="text-right">
                            <button type="button" class="btn btn-ghost btn-xs text-error btn-square" wire:click="deleteParticipant('{{ $participant->id }}')"
                                x-confirm="{ message: @js(__('Delete participant :name?', ['name' => $participant->full_name])), tone: 'error', confirm: @js(__('Delete')) }"
                                aria-label="{{ __('Delete') }}">
                                <x-ui.icon name="trash" class="size-4" />
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9"><x-ui.empty-state icon="users" :title="__('No participants found')" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showAddModal)
        <dialog class="modal modal-open">
            <div class="modal-box max-w-lg">
                <div class="flex items-center justify-between pb-3 border-b border-base-200">
                    <h3 class="text-lg font-bold flex items-center gap-2">
                        <x-ui.icon name="user-group" class="size-5 text-primary" />
                        {{ __('Add Participant') }}
                    </h3>
                    <button type="button" class="btn btn-ghost btn-sm btn-circle" wire:click="closeAddModal">✕</button>
                </div>

                <form wire:submit="addParticipant" class="space-y-4 pt-4">
                    <x-ui.input :label="__('Full name')" name="full_name" wire:model="full_name" required autofocus />

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-ui.input :label="__('Date of birth')" name="birth_date" type="date" wire:model="birth_date" required />

                        <x-ui.select :label="__('Gender')" name="gender" wire:model="gender" :options="['L' => __('Male'), 'P' => __('Female')]" required />
                    </div>

                    <x-ui.input :label="__('KTA number')" name="kta_number" wire:model="kta_number" :hint="__('Optional, leave blank if not available.')" placeholder="contoh: 12.01.00.0001" />

                    <div class="modal-action pt-2">
                        <button type="button" class="btn btn-ghost" wire:click="closeAddModal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.class="pointer-events-none opacity-70">
                            <span wire:loading wire:target="addParticipant" class="loading loading-spinner loading-sm"></span>
                            {{ __('Save') }}
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="closeAddModal"><button type="button">close</button></div>
        </dialog>
    @endif
</x-ui.card>
