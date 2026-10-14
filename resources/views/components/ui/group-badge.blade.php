@props(['number' => null, 'size' => 'sm', 'label' => false])

@php
    $color = \App\Support\GroupColor::for($number);
@endphp

{{-- Badge nomor grup dengan warna khusus per grup; print-color-adjust agar warna ikut tercetak --}}
@if ($number)
    <span
        {{ $attributes->class(['badge border-0 font-semibold whitespace-nowrap [print-color-adjust:exact]', "badge-{$size}"]) }}
        style="background-color: {{ $color['background'] }}; color: {{ $color['text'] }};"
    >{{ $label ? __('Group :n', ['n' => $number]) : $number }}</span>
@else
    <span {{ $attributes->class(['text-base-content/40']) }}>-</span>
@endif
