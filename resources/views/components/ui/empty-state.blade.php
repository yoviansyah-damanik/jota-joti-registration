@props(['icon' => 'info', 'title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-2 py-10 text-center']) }}>
    <x-ui.icon :name="$icon" class="text-base-content/30 size-10" />
    <p class="font-medium">{{ $title }}</p>
    @if ($description)
        <p class="text-base-content/60 max-w-sm text-sm">{{ $description }}</p>
    @endif
    {{ $slot }}
</div>
