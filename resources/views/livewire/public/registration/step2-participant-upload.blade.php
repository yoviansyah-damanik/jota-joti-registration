@php
    $minimum = app(\App\Services\SettingService::class)->minParticipantsPerTroop();
    $guide = [
        __('Download the template or enter manually'),
        __('Fill in at least :count participants', ['count' => $minimum]),
        __('Check the list before continuing'),
    ];
@endphp

<x-ui.step-card :step="2" :title="__('Participant Data')" :subtitle="__('Upload the participant Excel file or add participants manually. NTA/KTA is optional.')">
    <x-slot:actions>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" class="btn btn-primary btn-sm" wire:click="openManualModal">
                <x-ui.icon name="plus" class="size-4" /> {{ __('Add Participant Manually') }}
            </button>
            <a href="{{ route('template.participants') }}" class="btn btn-soft btn-primary btn-sm">
                <x-ui.icon name="download" class="size-4" /> {{ __('Download Excel template') }}
            </a>
        </div>
    </x-slot:actions>

    <form wire:submit="save" class="space-y-6">
        {{-- Tiga langkah singkat --}}
        <ol class="grid gap-3 sm:grid-cols-3">
            @foreach ($guide as $text)
                <li class="bg-base-200/70 rounded-box flex items-center gap-3 p-4">
                    <span class="text-primary text-2xl font-extrabold tabular-nums">{{ $loop->iteration }}</span>
                    <span class="text-sm font-semibold leading-snug">{{ $text }}</span>
                </li>
            @endforeach
        </ol>

        <x-ui.file-upload
            :label="__('Participant Excel file')"
            name="participantFile"
            accept=".xlsx,.xls"
            icon="table"
            :file="$participantFile ? null : $savedFileName"
            :hint="__('Format .xlsx, max 5 MB.')"
            :required="$rows === []"
        />

        <div wire:loading wire:target="participantFile" class="text-base-content/60 flex items-center gap-2 text-sm">
            <span class="loading loading-spinner loading-sm"></span> {{ __('Checking file...') }}
        </div>

        @include('livewire.public.registration.partials.participant-preview')

        <div class="flex items-center justify-between pt-2">
            <button type="button" class="btn btn-ghost" wire:click="$parent.goTo(1)">
                <x-ui.icon name="arrow-left" class="size-4" /> {{ __('Back') }}
            </button>
            <button type="submit" class="btn btn-primary btn-lg" wire:loading.class="pointer-events-none opacity-70" wire:target="save" @disabled(! $this->sheetIsValid())>
                <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                {{ __('Save & Continue') }} <x-ui.icon name="arrow-right" class="size-5" />
            </button>
        </div>
    </form>

    @if ($showManualModal)
        <dialog class="modal modal-open">
            <div class="modal-box max-w-lg">
                <div class="flex items-center justify-between pb-3 border-b border-base-200">
                    <h3 class="text-lg font-bold flex items-center gap-2">
                        <x-ui.icon name="user-group" class="size-5 text-primary" />
                        {{ __('Add Participant Manually') }}
                    </h3>
                    <button type="button" class="btn btn-ghost btn-sm btn-circle" wire:click="closeManualModal">✕</button>
                </div>

                <form wire:submit="addManualParticipant" class="space-y-4 pt-4">
                    <x-ui.input :label="__('Full name')" name="manualFullName" wire:model="manualFullName" required autofocus />

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-ui.input :label="__('Date of birth')" name="manualBirthDate" type="date" wire:model="manualBirthDate" required />

                        <x-ui.select :label="__('Gender')" name="manualGender" wire:model="manualGender" :options="['L' => __('Male'), 'P' => __('Female')]" required />
                    </div>

                    <x-ui.input :label="__('KTA number')" name="manualKta" wire:model="manualKta" :hint="__('Optional, leave blank if not available.')" placeholder="contoh: 12.01.00.0001" />

                    <div class="modal-action pt-2">
                        <button type="button" class="btn btn-ghost" wire:click="closeManualModal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('Add') }}
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="closeManualModal"><button type="button">close</button></div>
        </dialog>
    @endif
</x-ui.step-card>
