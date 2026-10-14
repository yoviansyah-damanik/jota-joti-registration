@php
    $requirements = [
        ['icon' => 'flag', 'title' => __('Troop identity'), 'description' => __('Troop (gudep) number and full troop name, plus the name and phone number of the person in charge.')],
        ['icon' => 'table', 'title' => __('Participant data (Excel)'), 'description' => __('Fill in the provided template: KTA number, full name, date of birth, and gender. Minimum :count participants per troop.', ['count' => $settings->minParticipantsPerTroop()])],
        ['icon' => 'document', 'title' => __('Assignment letter'), 'description' => __('Signed assignment letter from the troop leader, in PDF/JPG/PNG format (max 2 MB).')],
        ['icon' => 'banknotes', 'title' => __('Proof of payment'), 'description' => __('Transfer receipt for the registration fee, in PDF/JPG/PNG format (max 2 MB).')],
    ];

    $steps = [
        ['icon' => 'clipboard-check', 'title' => __('Prepare requirements')],
        ['icon' => 'upload', 'title' => __('Fill in & upload data')],
        ['icon' => 'check-circle', 'title' => __('Wait for verification')],
        ['icon' => 'qr', 'title' => __('Print participant cards')],
    ];
@endphp

<div class="space-y-8">
    {{-- Hero --}}
    <section class="hero-split relative left-1/2 -mt-8 w-screen -translate-x-1/2">
        <div class="mx-auto grid max-w-6xl lg:grid-cols-[1.2fr_1fr]">
        {{-- kiri: pesan utama --}}
        <div class="hero-dark text-neutral-content flex flex-col justify-between gap-12 lg:bg-transparent p-6 sm:p-10 lg:p-14">
            <div>
                <div class="flex flex-wrap items-center gap-3 text-sm font-bold">
                    <span class="text-accent">{{ __('Registration') }}</span>
                    <span class="flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold">
                        <span @class(['size-2 rounded-full', 'bg-success' => $settings->registrationOpen(), 'bg-error' => ! $settings->registrationOpen()])></span>
                        {{ $settings->registrationOpen() ? __('Registration is open') : __('Registration is closed') }}
                    </span>
                </div>

                <h1 class="mt-5 text-4xl font-extrabold leading-[1.05] tracking-tight text-white sm:text-5xl xl:text-6xl">{{ $settings->get('event_name') }}</h1>
                <p class="text-neutral-content/75 mt-2 text-lg font-medium">{{ $settings->get('organization_name') }}</p>

                <p class="text-neutral-content/75 mt-5 max-w-xl text-base leading-relaxed sm:text-lg">
                    {{ __('Registration is done per troop (gugus depan) without an account. Prepare all the requirements below before you continue.') }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#requirements" class="btn btn-accent btn-lg">
                        {{ __('Register Now') }} <x-ui.icon name="arrow-right" class="size-5" />
                    </a>
                    <a href="{{ route('status') }}" wire:navigate class="btn btn-lg border-0 bg-white/10 text-white hover:bg-white/20">
                        <x-ui.icon name="search" class="size-5" /> {{ __('Check Status') }}
                    </a>
                </div>
            </div>

            <dl class="grid grid-cols-3 gap-4">
                @foreach ([
                    ['value' => $stats['troops'], 'label' => __('Verified troops')],
                    ['value' => $stats['participants'], 'label' => __('Registered participants')],
                    ['value' => $stats['groups'], 'label' => __('Group numbers')],
                ] as $stat)
                    <div>
                        <dd class="text-3xl font-extrabold tabular-nums text-white sm:text-4xl">{{ number_format($stat['value'], 0, ',', '.') }}</dd>
                        <dt class="text-neutral-content/65 mt-1 text-xs leading-snug sm:text-sm">{{ $stat['label'] }}</dt>
                    </div>
                @endforeach
            </dl>
        </div>

        {{-- kanan: logo besar di atas warna utama --}}
        <div class="hero-purple order-first flex items-center justify-center lg:bg-transparent p-8 sm:p-12 lg:order-last">
            <x-ui.logo class="h-auto w-44 sm:w-64 lg:w-full lg:max-w-sm" />
        </div>
        </div>
    </section>

    @if ($certificatesPublished)
        <a href="{{ route('status') }}" wire:navigate
            class="rounded-box bg-accent/15 hover:bg-accent/25 group flex flex-col gap-5 p-5 transition sm:flex-row sm:items-center sm:gap-6 sm:p-6">
            <span class="bg-accent/30 text-accent-content flex size-16 shrink-0 items-center justify-center rounded-2xl">
                <x-ui.icon name="sparkles" class="size-9" />
            </span>

            <span class="min-w-0 flex-1">
                <span class="block text-xl font-extrabold tracking-tight sm:text-2xl">{{ __('E-certificates have been issued!') }}</span>
                <span class="text-base-content/75 mt-1 block">{{ __('The event is completed. Enter your registration code on the status page to download your troop and participant certificates.') }}</span>
            </span>

            <span class="btn btn-accent btn-lg sm:shrink-0">
                {{ __('Download E-Certificates') }} <x-ui.icon name="arrow-right" class="size-5 transition group-hover:translate-x-1" />
            </span>
        </a>
    @elseif ($groupsCompleted)
        <a href="{{ route('cards.index') }}" wire:navigate
            class="rounded-box bg-success/10 hover:bg-success/15 group flex flex-col gap-5 p-5 transition sm:flex-row sm:items-center sm:gap-6 sm:p-6">
            <span class="bg-success/20 text-success flex size-16 shrink-0 items-center justify-center rounded-2xl">
                <x-ui.icon name="qr" class="size-9" />
            </span>

            <span class="min-w-0 flex-1">
                <span class="block text-xl font-extrabold tracking-tight text-[oklch(33%_0.09_155)] sm:text-2xl">{{ __('Participant cards are ready to print!') }}</span>
                <span class="mt-1 block text-[oklch(40%_0.07_155)]">{{ __('Groups have been assigned. Find your troop and print your participant cards now.') }}</span>
                <span class="mt-3 flex flex-wrap gap-2 text-sm font-semibold text-[oklch(33%_0.09_155)]">
                    <span class="bg-success/15 rounded-full px-3 py-1">{{ number_format($stats['troops'], 0, ',', '.') }} {{ __('troops') }}</span>
                    <span class="bg-success/15 rounded-full px-3 py-1">{{ __(':count participants', ['count' => number_format($stats['participants'], 0, ',', '.')]) }}</span>
                </span>
            </span>

            <span class="btn btn-success btn-lg sm:shrink-0">
                {{ __('Print participant cards') }} <x-ui.icon name="arrow-right" class="size-5 transition group-hover:translate-x-1" />
            </span>
        </a>
    @else
        <x-ui.alert type="info">{{ __('Participant cards are not available yet. They can be printed after the group assignment has been generated.') }}</x-ui.alert>
    @endif

    @unless ($settings->registrationOpen())
        <x-ui.alert type="warning">{{ __('Registration is currently closed.') }}</x-ui.alert>
    @endunless

    {{-- Alur pendaftaran --}}
    <section>
        <ol class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ($steps as $step)
                <li class="bg-base-100 shadow-soft rounded-box flex items-center gap-3 px-4 py-3.5">
                    <span class="text-primary text-2xl font-extrabold tabular-nums">{{ $loop->iteration }}</span>
                    <p class="text-sm font-semibold leading-snug">{{ $step['title'] }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Persyaratan --}}
    <section id="requirements" class="scroll-mt-6 space-y-4">
        <div>
            <h2 class="text-xl font-bold sm:text-2xl">{{ __('Registration Requirements') }}</h2>
            <p class="text-base-content/60 text-sm">{{ __('Make sure every item below is ready before you start.') }}</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($requirements as $index => $item)
                <x-ui.card :title="($index + 1).'. '.$item['title']" :icon="$item['icon']" >
                    <p class="text-base-content/70 text-sm">{{ $item['description'] }}</p>

                    @if ($item['icon'] === 'table')
                        <div>
                            <a href="{{ route('template.participants') }}" class="btn btn-secondary btn-sm">
                                <x-ui.icon name="download" class="size-4" /> {{ __('Download Excel template') }}
                            </a>
                        </div>
                    @endif

                    @if ($item['icon'] === 'banknotes')
                        <x-ui.fee-summary :fee="(int) $settings->get('registration_fee')" :bank="$settings->get('bank_account_info')" example />
                    @endif
                </x-ui.card>
            @endforeach
        </div>
    </section>

    @if ($additionalTerms->isNotEmpty())
        <x-ui.card :title="__('Other terms')" icon="info">
            <ul class="list-disc space-y-1 pl-5 text-sm">
                @foreach ($additionalTerms as $term)
                    <li>{{ $term }}</li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif

    {{-- Persetujuan & lanjut --}}
    <x-ui.card>
        <form wire:submit="proceed" class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <label class="flex cursor-pointer items-start gap-3">
                    <input type="checkbox" wire:model="agreed" class="checkbox checkbox-primary mt-0.5 shrink-0" @disabled(! $settings->registrationOpen()) />
                    <span class="text-base-content text-sm font-medium sm:text-base">{{ __('I have prepared all the requirements above.') }}</span>
                </label>
                @error('agreed') <p class="text-error mt-1 text-sm">{{ $message }}</p> @enderror
                @if ($settings->get('contact_info'))
                    <p class="text-base-content/60 mt-2 text-xs sm:pl-8">{{ __('Questions?') }} {{ $settings->get('contact_info') }}</p>
                @endif
            </div>
            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end lg:shrink-0">
                <a href="{{ route('status') }}" wire:navigate class="btn btn-outline">{{ __('Check Status') }}</a>
                <button type="submit" class="btn btn-primary" @disabled(! $settings->registrationOpen())>
                    {{ __('Continue to Registration') }} <x-ui.icon name="arrow-right" class="size-4" />
                </button>
            </div>
        </form>
    </x-ui.card>

</div>
