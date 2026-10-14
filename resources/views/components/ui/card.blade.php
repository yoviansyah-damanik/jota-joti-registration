@props(['title' => null, 'subtitle' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'card bg-base-100']) }}>
    <div class="card-body gap-4 p-5 sm:p-6">
        @if ($title || isset($actions))
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex min-w-0 items-start gap-3">
                    @if ($icon)
                        <span class="bg-primary/10 text-primary flex size-10 shrink-0 items-center justify-center rounded-xl">
                            <x-ui.icon :name="$icon" class="size-5" />
                        </span>
                    @endif
                    <div class="min-w-0">
                        @if ($title)
                            <h2 class="card-title text-base font-bold leading-snug">{{ $title }}</h2>
                        @endif
                        @if ($subtitle)
                            <p class="text-base-content/60 mt-0.5 text-sm">{{ $subtitle }}</p>
                        @endif
                    </div>
                </div>
                @isset($actions)
                    <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
                @endisset
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
