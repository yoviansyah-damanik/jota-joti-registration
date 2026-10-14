<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="jotajoti">
<head>
    @include('layouts.partials.head')
</head>
<body class="bg-base-200 relative isolate flex min-h-screen flex-col antialiased">
    <div class="from-primary/[0.12] via-secondary/[0.04] pointer-events-none absolute inset-x-0 top-0 -z-10 h-96 bg-gradient-to-b to-transparent"></div>

    <main class="grid flex-1 place-items-center p-4">
        <div class="w-full max-w-sm">
            {{ $slot }}
        </div>
    </main>

    @include('layouts.partials.footer')
    @include('layouts.partials.toast')
</body>
</html>
