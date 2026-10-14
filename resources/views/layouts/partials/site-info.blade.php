@php
    $links = [
        ['url' => 'https://jotajoti.info', 'label' => 'jotajoti.info', 'logo' => 'images/partners/jotajoti.svg', 'desc' => __('Official JOTA-JOTI website')],
        ['url' => 'https://pramuka.or.id', 'label' => 'pramuka.or.id', 'logo' => 'images/partners/pramuka.png', 'desc' => __('Kwartir Nasional Gerakan Pramuka')],
    ];
@endphp

<footer class="bg-neutral text-neutral-content mt-10 w-full no-print">
    <div class="mx-auto grid w-full max-w-6xl gap-10 px-4 py-12 md:grid-cols-2 lg:grid-cols-[1.3fr_1fr_1fr]">
        {{-- identitas & deskripsi situs --}}
        <div class="md:col-span-2 lg:col-span-1">
            <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center gap-3">
                <x-ui.logo class="size-14" />
                <span class="leading-tight">
                    <span class="block text-lg font-extrabold text-white">{{ $eventName }}</span>
                    <span class="text-neutral-content/70 block text-sm">{{ $organizationName }}</span>
                </span>
            </a>
            <p class="text-neutral-content/70 mt-5 max-w-md text-sm leading-relaxed">
                {{ __('This site is the online registration portal for troops (gugus depan) taking part in JOTA-JOTI. Register your troop, upload participant data, track verification and print participant cards here.') }}
            </p>
        </div>

        {{-- situs terkait --}}
        <div>
            <h3 class="text-accent text-xs font-bold uppercase tracking-wider">{{ __('Related sites') }}</h3>
            <ul class="mt-4 space-y-4">
                @foreach ($links as $link)
                    <li>
                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3">
                            <span class="flex h-12 w-28 shrink-0 items-center justify-center rounded-lg bg-white p-2">
                                <img src="{{ asset($link['logo']) }}" alt="{{ $link['label'] }}" class="max-h-full w-auto object-contain" loading="lazy" />
                            </span>
                            <span class="min-w-0 text-sm leading-tight">
                                <span class="block font-semibold text-white group-hover:underline">{{ $link['label'] }}</span>
                                <span class="text-neutral-content/60 block text-xs">{{ $link['desc'] }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- aplikasi & media sosial --}}
        <div>
            <h3 class="text-accent text-xs font-bold uppercase tracking-wider">{{ __('Get connected') }}</h3>
            <p class="mt-4 text-sm font-semibold text-white">{{ __('Download AyoPramuka') }}</p>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                @foreach ([
                    ['url' => 'https://play.google.com/store/apps/details?id=id.pramuka.ayosatu.app', 'caption' => 'GET IT ON', 'name' => 'Google Play', 'icon' => 'M22.018 13.298l-3.919 2.218-3.515-3.493 3.543-3.521 3.891 2.202a1.49 1.49 0 0 1 0 2.594zM1.337.924a1.486 1.486 0 0 0-.112.568v21.017c0 .217.045.419.124.6l11.155-11.087L1.337.924zm12.207 10.065l3.258-3.238L3.45.195a1.466 1.466 0 0 0-.946-.179l11.04 10.973zm0 2.067l-11 10.933c.298.036.612-.016.906-.183l13.324-7.54-3.23-3.21z'],
                    ['url' => 'https://apps.apple.com/id/app/ayopramuka/id1639075094', 'caption' => 'Download on the', 'name' => 'App Store', 'icon' => 'M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701'],
                ] as $store)
                    <a href="{{ $store['url'] }}" target="_blank" rel="noopener noreferrer"
                        class="flex h-12 items-center gap-2.5 rounded-lg border border-white/25 bg-black px-3.5 text-white transition hover:border-white/60">
                        <svg class="size-7 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $store['icon'] }}" /></svg>
                        <span class="flex flex-col leading-none">
                            <span class="text-[9px] uppercase tracking-wide opacity-80">{{ $store['caption'] }}</span>
                            <span class="mt-0.5 text-base font-semibold">{{ $store['name'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-4">
                <a href="https://www.instagram.com/pramuka.padangsidimpuan" target="_blank" rel="noopener noreferrer" class="btn btn-sm border-0 bg-white/10 text-white hover:bg-white/20">
                    <x-ui.icon name="instagram" class="size-4" /> @pramuka.padangsidimpuan
                </a>
            </div>
            <p class="text-neutral-content/55 mt-4 max-w-xs text-xs leading-relaxed">{{ __('AyoPramuka is for registered Scout members with a QR-code member card.') }}</p>
        </div>
    </div>

    {{-- baris hak cipta --}}
    <div class="border-t border-white/10">
        <p class="text-neutral-content/60 mx-auto max-w-6xl px-4 py-5 text-center text-xs">
            @include('layouts.partials.copyright')
        </p>
    </div>
</footer>
