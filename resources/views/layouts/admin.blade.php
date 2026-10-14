<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="jotajoti">
<head>
    @include('layouts.partials.head')
</head>
<body class="bg-base-200 min-h-screen antialiased">
    <div class="drawer lg:drawer-open">
        <input id="admin-drawer" type="checkbox" class="drawer-toggle" />

        <div class="drawer-content flex min-h-screen min-w-0 flex-col">
            <header class="navbar bg-base-100 sticky top-0 z-30 min-h-16 gap-2 px-3 shadow-sm lg:hidden no-print">
                <label for="admin-drawer" class="btn btn-ghost btn-square" aria-label="{{ __('Menu') }}">
                    <x-ui.icon name="menu" class="size-6" />
                </label>
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex min-w-0 items-center gap-2.5">
                    <x-ui.logo class="size-10" />
                    <span class="flex min-w-0 flex-col leading-tight">
                        <span class="truncate text-base font-extrabold uppercase tracking-wide">{{ config('app.brand_title') }}</span>
                        <span class="text-base-content/70 truncate text-[11px] font-medium">{{ __('Admin') }} · {{ config('app.brand_subtitle') }}</span>
                    </span>
                </a>
            </header>

            <main class="w-full flex-1 p-4 lg:p-8">
                {{ $slot }}
            </main>

            @include('layouts.partials.footer')
        </div>

        <div class="drawer-side z-40 no-print">
            <label for="admin-drawer" class="drawer-overlay"></label>
            @include('layouts.partials.admin-sidebar')
        </div>
    </div>

    <x-ui.confirm-modal />
    @include('layouts.partials.toast')
</body>
</html>
