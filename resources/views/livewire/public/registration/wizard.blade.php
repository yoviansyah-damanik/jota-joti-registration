@php
    $total = count($steps);
@endphp

<div class="space-y-6">
    <x-ui.page-header :title="__('Registration Form')" :subtitle="__('Complete each step. Your progress is kept in this browser until you submit.')" />

    {{-- Ringkasan progres untuk layar kecil --}}
    <div class="bg-base-100 rounded-box shadow-soft space-y-3 p-4 lg:hidden">
        <div class="flex items-baseline justify-between gap-3">
            <p class="font-bold">{{ $steps[$step]['title'] }}</p>
            <p class="text-base-content/60 text-sm">{{ __('Step :n of :total', ['n' => $step, 'total' => $total]) }}</p>
        </div>
        <progress class="progress progress-primary h-2 w-full" value="{{ $step }}" max="{{ $total }}"></progress>
    </div>

    <div class="grid gap-6 lg:grid-cols-[19rem_minmax(0,1fr)] lg:items-start">
        {{-- Panel langkah (desktop) --}}
        <aside class="hero-dark text-neutral-content rounded-box shadow-soft hidden p-5 lg:sticky lg:top-24 lg:block">
            <p class="text-accent px-3 pb-2 pt-1 text-sm font-bold">{{ __('Registration') }}</p>

            <ol class="space-y-1">
                @foreach ($steps as $number => $item)
                    @php
                        $done = $number < $reached;
                        $current = $number === $step;
                        $locked = $number > $reached;
                    @endphp
                    <li wire:key="rail-{{ $number }}">
                        <button
                            type="button"
                            wire:click="goTo({{ $number }})"
                            @disabled($locked)
                            @class([
                                'flex w-full items-start gap-4 rounded-xl px-3 py-3.5 text-left transition',
                                'bg-white/10' => $current,
                                'hover:bg-white/5' => ! $current && ! $locked,
                                'cursor-not-allowed opacity-45' => $locked,
                            ])
                            @if ($current) aria-current="step" @endif
                        >
                            <span @class([
                                'flex size-9 shrink-0 items-center justify-center rounded-full text-sm font-extrabold',
                                'bg-primary text-primary-content ring-2 ring-white/40' => $done,
                                'bg-accent text-accent-content' => $current && ! $done,
                                'bg-white/15' => ! $done && ! $current,
                            ])>
                                @if ($done)
                                    <x-ui.icon name="check" class="size-5" />
                                @else
                                    {{ $number }}
                                @endif
                            </span>
                            <span class="min-w-0">
                                <span class="block font-bold text-white">{{ $item['title'] }}</span>
                                <span class="text-neutral-content/65 mt-0.5 block text-sm leading-snug">{{ $item['description'] }}</span>
                            </span>
                        </button>
                    </li>
                @endforeach
            </ol>

            @if ($contact)
                <div class="mt-4 rounded-xl bg-white/5 p-4 text-sm">
                    <p class="font-bold text-white">{{ __('Need help?') }}</p>
                    <p class="text-neutral-content/70 mt-1">{{ $contact }}</p>
                </div>
            @endif
        </aside>

        {{-- Setiap langkah adalah komponen lazy terpisah --}}
        <div class="min-w-0">
            @switch($step)
                @case(1)
                    <livewire:public.registration.step1-troop-data wire:key="step-1" />
                    @break
                @case(2)
                    <livewire:public.registration.step2-participant-upload wire:key="step-2" />
                    @break
                @case(3)
                    <livewire:public.registration.step3-documents wire:key="step-3" />
                    @break
                @default
                    <livewire:public.registration.step4-review wire:key="step-4" />
            @endswitch
        </div>
    </div>
</div>
