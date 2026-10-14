<x-ui.step-card :step="1" :title="__('Troop Data')" :subtitle="__('Enter the troop (gugus depan) number and full name.')">
    <form wire:submit="save" class="space-y-10">
        {{-- Identitas gugus depan --}}
        <section class="space-y-5">
            <h3 class="text-lg font-bold">{{ __('Troop identity') }}</h3>

            <div class="grid gap-x-5 gap-y-2 lg:grid-cols-[minmax(0,21rem)_minmax(0,1fr)]">
                {{-- nilai dikelola Alpine: format xx-xxx saat mengetik, nomor kedua = nomor ganjil + 1 --}}
                <div class="grid grid-cols-2 gap-x-3" x-data="troopNumberPair($wire.entangle('troop_number'))">
                    <x-ui.input
                        :label="__('Troop number')"
                        name="troop_number"
                        x-bind:value="first"
                        x-on:input="update($event.target)"
                        placeholder="01-083"
                        inputmode="numeric"
                        autocomplete="off"
                        class="input-lg font-bold tabular-nums"
                        required
                    />
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">&amp;</legend>
                        <input type="text" class="input input-lg w-full font-bold tabular-nums opacity-70" x-bind:value="pair" placeholder="xx-xxx" readonly tabindex="-1" />
                    </fieldset>
                    <p x-show="isEven" x-cloak class="text-error col-span-2 -mt-1 text-xs">{{ __('The first troop number must be odd, e.g. 01-083.') }}</p>
                </div>

                <x-ui.input :label="__('Troop name')" name="troop_name" wire:model="troop_name" :placeholder="__('e.g. SMP Negeri 1 Padangsidimpuan')" class="input-lg" required />
            </div>

            {{-- Satuan: pilihan besar agar jelas dan mudah disentuh di HP --}}
            <fieldset class="space-y-2" x-data="{ chosen: @js($unit) }" x-on:change="chosen = $event.target.value">
                <legend class="text-sm font-semibold">{{ __('Unit') }} <span class="text-error">*</span></legend>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach ($units as $unitOption)
                        <label class="rounded-box bg-base-200 has-[:checked]:bg-primary/10 has-[:checked]:ring-primary has-[:focus-visible]:ring-primary flex cursor-pointer items-center gap-4 p-4 transition has-[:checked]:ring-2 has-[:focus-visible]:ring-2" wire:key="unit-{{ $unitOption->value }}">
                            <input type="radio" class="radio radio-primary" wire:model="unit" value="{{ $unitOption->value }}" name="unit" />
                            <span>
                                <span class="block text-lg font-bold">{{ $unitOption->label() }}</span>
                                <span class="text-base-content/60 block text-sm">{{ $unitOption->description() }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('unit')
                    <p x-show="! chosen" class="text-error text-sm">{{ $message }}</p>
                @enderror
            </fieldset>
        </section>

        {{-- Penanggung jawab --}}
        <section class="space-y-5">
            <h3 class="text-lg font-bold">{{ __('Person in charge') }}</h3>

            <x-ui.alert type="info" class="text-sm">
                {{ __('The person in charge should preferably be the Troop / Unit Leader (Pembina Gugus Depan / Satuan).') }}
            </x-ui.alert>

            <div class="grid gap-x-5 md:grid-cols-3">
                <x-ui.input :label="__('Name')" name="pic_name" wire:model="pic_name" required />
                <x-ui.input :label="__('Phone / WhatsApp')" name="pic_phone" wire:model="pic_phone" placeholder="08xxxxxxxxxx" required />
                <x-ui.input :label="__('Email')" name="pic_email" type="email" wire:model="pic_email" :hint="__('Optional')" />
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary btn-lg" wire:loading.attr="disabled">
                <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                {{ __('Save & Continue') }} <x-ui.icon name="arrow-right" class="size-5" />
            </button>
        </div>
    </form>
</x-ui.step-card>
