<div class="space-y-6">
    {{-- Hero --}}
    <section class="rounded-box shadow-soft grid overflow-hidden lg:grid-cols-[1.2fr_1fr]">
        <div class="hero-dark text-neutral-content flex flex-col justify-between gap-10 p-6 sm:p-10 lg:p-14">
            <div>
                <p class="text-accent text-sm font-bold">{{ __('Participant cards') }}</p>
                <h1 class="mt-3 text-3xl font-extrabold leading-tight tracking-tight text-white sm:text-4xl xl:text-5xl">{{ __('Print Participant Cards') }}</h1>
                <p class="text-neutral-content/75 mt-3 max-w-xl text-base leading-relaxed sm:text-lg">
                    @if ($available)
                        {{ __('Groups have been assigned. Find your troop to view and print its participant cards.') }}
                    @else
                        {{ __('Cards can be printed after the committee has generated the group assignment. Please check back later.') }}
                    @endif
                </p>
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

        {{-- ilustrasi kartu mini --}}
        <div class="hero-purple order-first flex items-center justify-center overflow-hidden p-8 sm:p-12 lg:order-last">
            <div class="rounded-box w-40 rotate-3 overflow-hidden bg-white shadow-2xl sm:w-48" aria-hidden="true">
                <div class="bg-accent relative h-24 p-3 sm:h-28">
                    <span class="absolute -right-6 -top-6 size-24 rounded-full bg-white/25"></span>
                    <span class="text-accent-content relative grid size-14 place-items-center rounded-full bg-white text-3xl font-extrabold sm:size-16 sm:text-4xl">3</span>
                </div>
                <div class="space-y-2 p-3">
                    <span class="bg-base-300 block h-3 w-4/5 rounded-full"></span>
                    <span class="bg-base-200 block h-2 w-3/5 rounded-full"></span>
                    <div class="mx-auto mt-3 grid w-24 grid-cols-7 gap-0.5 sm:w-28">
                        @foreach ([1,1,1,0,1,1,1, 1,0,1,1,1,0,1, 1,1,1,0,0,1,1, 0,1,0,1,1,0,0, 1,0,1,1,0,1,1, 1,0,1,0,1,0,1, 1,1,1,1,0,1,1] as $cell)
                            <span @class(['aspect-square', 'bg-neutral' => $cell, 'bg-transparent' => ! $cell])></span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($available)
        <livewire:public.card-center lazy />
    @else
        <section class="rounded-box bg-base-100 shadow-soft flex flex-col items-center gap-3 p-10 text-center">
            <span class="bg-warning/25 text-warning-content grid size-14 place-items-center rounded-full"><x-ui.icon name="exclamation" class="size-7" /></span>
            <h2 class="text-xl font-extrabold">{{ __('Participant cards are not available yet.') }}</h2>
            <p class="text-base-content/65 max-w-md text-sm">{{ __('Cards can be printed after the committee has generated the group assignment. Please check back later.') }}</p>
        </section>
    @endif

    <div class="text-center">
        <a href="{{ route('home') }}" wire:navigate class="btn btn-ghost btn-sm">
            <x-ui.icon name="arrow-left" class="size-4" /> {{ __('Back to home') }}
        </a>
    </div>
</div>
