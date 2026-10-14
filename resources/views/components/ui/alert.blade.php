@props(['type' => 'info', 'icon' => null])

@php
    $icons = ['info' => 'info', 'success' => 'check-circle', 'warning' => 'exclamation', 'error' => 'x-circle'];
@endphp

<div role="alert" {{ $attributes->class(['alert alert-soft', "alert-{$type}"]) }}>
    <x-ui.icon :name="$icon ?? $icons[$type]" />
    <div>{{ $slot }}</div>
</div>
