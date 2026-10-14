@props(['fee', 'count' => null, 'example' => false, 'bank' => null])

@php
    // biaya dihitung per peserta; $count null = hanya tampilkan tarif (atau contoh bila $example)
    $exampleCount = 5;
    $shown = $count ?? ($example ? $exampleCount : null);
@endphp

{{-- Kartu tagihan: tarif per peserta, rumus perhitungan, dan tujuan transfer --}}
<div {{ $attributes->class(['rounded-box border-primary/25 bg-primary/5 overflow-hidden border border-dashed']) }}>
    <div class="flex items-end justify-between gap-3 px-4 pb-3 pt-4">
        <div>
            <p class="text-base-content/60 text-[11px] font-bold uppercase tracking-wider">{{ __('Fee per participant') }}</p>
            <p class="mt-0.5 text-3xl font-extrabold tabular-nums tracking-tight">{{ \App\Support\Rupiah::format($fee) }}</p>
        </div>
        <span class="badge badge-primary badge-soft whitespace-nowrap">/ {{ __('participant') }}</span>
    </div>

    @if ($shown !== null)
        <div class="border-primary/20 border-t border-dashed px-4 py-3">
            <p class="text-base-content/60 text-xs font-medium">{{ $count === null ? __('Example: :count participants', ['count' => $shown]) : __('Total to transfer') }}</p>

            <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 tabular-nums">
                <span class="bg-base-100 rounded-lg px-2.5 py-1 text-sm font-bold shadow-sm">{{ $shown }} <span class="text-base-content/60 font-medium">{{ __('participant') }}</span></span>
                <span class="text-base-content/50 font-bold">&times;</span>
                <span class="bg-base-100 rounded-lg px-2.5 py-1 text-sm font-bold shadow-sm">{{ \App\Support\Rupiah::format($fee) }}</span>
                <span class="text-base-content/50 font-bold">=</span>
                <span class="bg-primary text-primary-content rounded-lg px-3 py-1 text-base font-extrabold shadow-sm">{{ \App\Support\Rupiah::format($shown * $fee) }}</span>
            </div>
        </div>
    @endif

    @if ($bank)
        <div class="bg-base-100/70 border-primary/20 flex items-start gap-3 border-t border-dashed px-4 py-3">
            <span class="bg-primary/10 text-primary grid size-9 shrink-0 place-items-center rounded-full"><x-ui.icon name="banknotes" class="size-4" /></span>
            <div class="min-w-0">
                <p class="text-base-content/60 text-xs font-medium">{{ __('Transfer to') }}</p>
                <p class="whitespace-pre-line text-sm font-semibold leading-snug">{{ $bank }}</p>
            </div>
        </div>
    @endif
</div>
