@php
    $uploaded = $documents->filter(fn ($name) => $name !== '-')->count();
    $summary = [
        ['value' => $participants->count(), 'label' => __('Participants')],
        ['value' => $genderCounts['L'] ?? 0, 'label' => __('Male')],
        ['value' => $genderCounts['P'] ?? 0, 'label' => __('Female')],
        ['value' => $uploaded.'/'.$documents->count(), 'label' => __('Documents')],
    ];
@endphp

<x-ui.step-card :step="4" :title="__('Review & Submit')" :subtitle="__('Check everything, then submit')">
    <div class="space-y-10" x-data="documentViewer">
        @error('draft') <x-ui.alert type="error">{{ $message }}</x-ui.alert> @enderror

        {{-- Ringkasan angka --}}
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            @foreach ($summary as $item)
                <div class="bg-base-200/70 rounded-box p-4 sm:p-5">
                    <p class="text-primary text-3xl font-extrabold tabular-nums sm:text-4xl">{{ $item['value'] }}</p>
                    <p class="text-base-content/65 mt-1 text-sm font-medium">{{ $item['label'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-10 lg:grid-cols-2">
            {{-- Data gudep --}}
            <section class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-lg font-bold">{{ __('Troop Data') }}</h3>
                    <button type="button" class="btn btn-soft btn-primary btn-sm" wire:click="$parent.goTo(1)">{{ __('Edit') }}</button>
                </div>
                <dl class="grid grid-cols-[8rem_minmax(0,1fr)] gap-x-4 gap-y-3 text-sm sm:text-base">
                    <dt class="text-base-content/60">{{ __('Troop number') }}</dt>
                    <dd class="font-bold">{{ \App\Support\TroopNumber::label($troop['troop_number'] ?? '-') }}</dd>
                    <dt class="text-base-content/60">{{ __('Troop name') }}</dt>
                    <dd class="font-bold">{{ $troop['troop_name'] ?? '-' }}</dd>
                    <dt class="text-base-content/60">{{ __('Unit') }}</dt>
                    <dd>{{ \App\Enums\ScoutUnit::tryFrom($troop['unit'] ?? '')?->label() ?? '-' }}</dd>
                    <dt class="text-base-content/60">{{ __('Person in charge') }}</dt>
                    <dd>{{ $troop['pic_name'] ?? '-' }}</dd>
                    <dt class="text-base-content/60">{{ __('Phone') }}</dt>
                    <dd>{{ $troop['pic_phone'] ?? '-' }}</dd>
                    <dt class="text-base-content/60">{{ __('Email') }}</dt>
                    <dd class="break-all">{{ ($troop['pic_email'] ?? null) ?: '-' }}</dd>
                </dl>
            </section>

            {{-- Dokumen --}}
            <section class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-lg font-bold">{{ __('Documents') }}</h3>
                    <button type="button" class="btn btn-soft btn-primary btn-sm" wire:click="$parent.goTo(3)">{{ __('Edit') }}</button>
                </div>
                <ul class="space-y-2">
                    @foreach ($documents as $label => $name)
                        <li class="bg-base-200/70 rounded-box flex items-center gap-3 p-3.5">
                            <span @class([
                                'flex size-9 shrink-0 items-center justify-center rounded-full',
                                'bg-success/15 text-success' => $name !== '-',
                                'bg-error/10 text-error' => $name === '-',
                            ])>
                                <x-ui.icon :name="$name !== '-' ? 'check' : 'exclamation'" class="size-5" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm font-bold">{{ $label }}</span>
                                <span class="text-base-content/60 block truncate text-xs sm:text-sm">{{ $name !== '-' ? $name : __('Missing') }}</span>
                            </span>
                            @if ($preview = $previews[$label] ?? null)
                                <span class="ml-auto flex shrink-0 gap-1">
                                    @if ($preview['kind'])
                                        <button type="button" class="btn btn-soft btn-primary btn-xs" x-on:click="view(@js($preview))">
                                            <x-ui.icon name="eye" class="size-3.5" /> {{ __('Preview') }}
                                        </button>
                                    @else
                                        <a href="#review-participants" class="btn btn-soft btn-primary btn-xs">
                                            <x-ui.icon name="eye" class="size-3.5" /> {{ __('Preview') }}
                                        </a>
                                    @endif
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>

        {{-- Peserta --}}
        <section id="review-participants" class="scroll-mt-24 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-lg font-bold">{{ __('Participants') }}</h3>
                <button type="button" class="btn btn-soft btn-primary btn-sm" wire:click="$parent.goTo(2)">{{ __('Edit') }}</button>
            </div>
            <div class="rounded-box max-h-80 overflow-auto">
                <table class="table table-sm table-pin-rows">
                    <thead>
                        <tr><th>#</th><th>{{ __('KTA number') }}</th><th>{{ __('Full name') }}</th><th>{{ __('Date of birth') }}</th><th>{{ __('Gender') }}</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($participants as $participant)
                            <tr>
                                <td class="text-base-content/50">{{ $loop->iteration }}</td>
                                <td class="font-mono">{{ $participant['kta_number'] ?: '-' }}</td>
                                <td>{{ $participant['full_name'] }}</td>
                                <td>{{ $participant['birth_date'] }}</td>
                                <td>{{ $participant['gender'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Konfirmasi --}}
        <form wire:submit="submit" class="bg-primary/5 rounded-box flex flex-col gap-5 p-5 sm:p-6 md:flex-row md:items-center md:justify-between">
            <div class="min-w-0">
                <p class="font-bold">{{ __('Confirm and submit') }}</p>
                <label class="mt-2 flex cursor-pointer items-start gap-3">
                    <input type="checkbox" wire:model="confirmed" class="checkbox checkbox-primary mt-0.5 shrink-0" />
                    <span class="text-sm sm:text-base">{{ __('I confirm that the data above is correct.') }}</span>
                </label>
                @error('confirmed') <p class="text-error mt-1 text-sm">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn btn-primary btn-lg shrink-0" wire:loading.attr="disabled">
                <span wire:loading wire:target="submit" class="loading loading-spinner loading-sm"></span>
                {{ __('Submit Registration') }} <x-ui.icon name="arrow-right" class="size-5" />
            </button>
        </form>

        <x-ui.document-viewer />
    </div>
</x-ui.step-card>
