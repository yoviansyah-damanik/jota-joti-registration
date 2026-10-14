@props(['title', 'value', 'description' => null, 'icon' => null, 'tone' => 'primary', 'compact' => false])

@php
    // nama kelas ditulis lengkap agar terdeteksi Tailwind
    $tones = [
        'primary' => ['chip' => 'bg-primary/10 text-primary', 'value' => 'text-primary', 'bar' => 'bg-primary'],
        'secondary' => ['chip' => 'bg-secondary/10 text-secondary', 'value' => 'text-secondary', 'bar' => 'bg-secondary'],
        'accent' => ['chip' => 'bg-accent/25 text-accent-content', 'value' => 'text-base-content', 'bar' => 'bg-accent'],
        'info' => ['chip' => 'bg-info/10 text-info', 'value' => 'text-info', 'bar' => 'bg-info'],
        'success' => ['chip' => 'bg-success/10 text-success', 'value' => 'text-success', 'bar' => 'bg-success'],
        'warning' => ['chip' => 'bg-warning/25 text-warning-content', 'value' => 'text-base-content', 'bar' => 'bg-warning'],
        'error' => ['chip' => 'bg-error/10 text-error', 'value' => 'text-error', 'bar' => 'bg-error'],
    ];
    $style = $tones[$tone] ?? $tones['primary'];
@endphp

<div {{ $attributes->merge(['class' => 'bg-base-100 rounded-box shadow-soft flex items-start justify-between gap-3 p-5']) }}>

    <div class="min-w-0">
        <p class="text-base-content/65 text-sm font-medium">{{ $title }}</p>
        <p class="{{ $style['value'] }} mt-1 {{ $compact ? 'whitespace-nowrap text-2xl tabular-nums' : 'text-3xl' }} font-extrabold tracking-tight">{{ $value }}</p>
        @if ($description)
            <p class="text-base-content/60 mt-1 text-xs">{{ $description }}</p>
        @endif
    </div>

    @if ($icon)
        <span class="{{ $style['chip'] }} flex size-11 shrink-0 items-center justify-center rounded-xl">
            <x-ui.icon :name="$icon" class="size-6" />
        </span>
    @endif
</div>
