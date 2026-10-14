@props(['class' => 'size-10'])

{{-- Logo utama aplikasi (sumber: resources/images/logo.png) --}}
<img src="{{ asset('images/logo.webp') }}" alt="{{ config('app.name') }}" {{ $attributes->merge(['class' => $class.' object-contain shrink-0']) }} />
