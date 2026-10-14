@props(['step', 'title', 'subtitle' => null, 'total' => 4])

{{-- Panel utama satu langkah formulir pendaftaran --}}
<section {{ $attributes->merge(['class' => 'bg-base-100 rounded-box shadow-soft p-5 sm:p-8']) }}>
    <header class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-primary text-sm font-bold">{{ __('Step :n of :total', ['n' => $step, 'total' => $total]) }}</p>
            <h2 class="mt-1 text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $title }}</h2>
            @if ($subtitle)
                <p class="text-base-content/65 mt-2 max-w-2xl">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
        @endisset
    </header>

    {{ $slot }}
</section>
