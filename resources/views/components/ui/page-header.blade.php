@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-wrap items-end justify-between gap-4']) }}>
    <div class="min-w-0">
        <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="text-base-content/60 mt-1 text-sm sm:text-base">{{ $subtitle }}</p>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>
    @endif
</div>
