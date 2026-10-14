<div class="mx-auto max-w-xl">
    @if ($troop)
        <x-ui.card class="text-center">
            <div class="flex flex-col items-center gap-3">
                <span class="bg-success/15 text-success grid size-16 place-items-center rounded-full">
                    <x-ui.icon name="check" class="size-8" />
                </span>
                <h1 class="text-2xl font-semibold">{{ __('Registration Submitted') }}</h1>
                <p class="text-base-content/70 text-sm">
                    {{ __('Thank you, :name. Your registration for :troop (:count participants) will be reviewed by the committee.', ['name' => $troop->pic_name, 'troop' => $troop->troop_name, 'count' => $troop->participants_count]) }}
                </p>

                <div class="bg-base-200 rounded-box my-2 w-full p-5" x-data="{ copied: false }">
                    <p class="text-base-content/60 text-xs uppercase tracking-widest">{{ __('Registration code') }}</p>
                    <p class="font-mono text-3xl font-bold tracking-wider">{{ $troop->registration_code }}</p>
                    <button type="button" class="btn btn-ghost btn-xs mt-2"
                        x-on:click="navigator.clipboard.writeText('{{ $troop->registration_code }}'); copied = true; setTimeout(() => copied = false, 2000)">
                        <span x-show="!copied">{{ __('Copy code') }}</span>
                        <span x-show="copied" x-cloak>{{ __('Copied!') }}</span>
                    </button>
                </div>

                <x-ui.alert type="warning" class="text-left text-sm">
                    {{ __('Save this code. You only need this code to check the registration status.') }}
                </x-ui.alert>

                <div class="flex flex-wrap justify-center gap-2">
                    <a href="{{ route('register.receipt', $troop->registration_code) }}" class="btn btn-secondary">
                        <x-ui.icon name="download" class="size-4" /> {{ __('Download Receipt (PDF)') }}
                    </a>
                    <a href="{{ route('status', ['code' => $troop->registration_code]) }}" wire:navigate class="btn btn-primary">{{ __('Check Status') }}</a>
                </div>
            </div>
        </x-ui.card>
    @endif
</div>
