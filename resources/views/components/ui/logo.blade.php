@props(['class' => 'size-10'])

@php
    $logoUrl = app(\App\Services\SettingService::class)->logoUrl();
@endphp

{{-- Logo utama aplikasi (dinamis sesuai pengaturan resmi) --}}
<img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" {{ $attributes->merge(['class' => $class.' object-contain shrink-0']) }} />
