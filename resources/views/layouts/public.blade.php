<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="jotajoti">
<head>
    @include('layouts.partials.head')
</head>
<body class="bg-base-200 relative isolate flex min-h-screen flex-col overflow-x-clip antialiased">
    <header class="navbar bg-base-100 sticky top-0 z-30 min-h-16 px-3 shadow-sm sm:px-4 no-print">
        @php
            $navLinks = [
                ['route' => 'home', 'icon' => 'home', 'label' => __('Requirements')],
                ['route' => 'status', 'icon' => 'search', 'label' => __('Check Status')],
                ['route' => 'tasks.submit', 'icon' => 'upload', 'label' => __('Kirim Tugas')],
                ['route' => 'tasks.status', 'icon' => 'clipboard-check', 'label' => __('Cek Tugas')],
            ];
        @endphp

        <div class="flex w-full items-center justify-between gap-3 sm:px-4">
            <a href="{{ route('home') }}" wire:navigate class="flex min-w-0 items-center gap-2.5 font-semibold leading-tight sm:gap-3 sm:text-lg">
                <x-ui.logo class="size-10 sm:size-12" />
                <span class="flex min-w-0 flex-col">
                    <span class="truncate text-base font-extrabold uppercase tracking-wide sm:text-xl">{{ config('app.brand_title') }}</span>
                    <span class="text-base-content/70 truncate text-[11px] font-medium sm:text-sm">{{ config('app.brand_subtitle') }}</span>
                </span>
            </a>

            {{-- layar lebar: tautan berupa pil --}}
            <nav class="hidden shrink-0 items-center gap-2 sm:flex">
                @foreach ($navLinks as $link)
                    <a href="{{ route($link['route']) }}" wire:navigate @class([
                        'flex h-11 items-center gap-2 rounded-full px-5 text-base font-semibold transition',
                        'bg-primary text-primary-content shadow-md' => request()->routeIs($link['route']),
                        'text-base-content/70 hover:bg-primary/10 hover:text-primary' => ! request()->routeIs($link['route']),
                    ])>
                        <x-ui.icon :name="$link['icon']" class="size-5" /> {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- HP: menu tarik-turun agar merek tidak terdesak --}}
            <div class="dropdown dropdown-end shrink-0 sm:hidden">
                <button type="button" tabindex="0" class="btn btn-ghost btn-square" aria-label="{{ __('Menu') }}">
                    <x-ui.icon name="menu" class="size-6" />
                </button>
                <ul tabindex="0" class="menu dropdown-content bg-base-100 rounded-box shadow-lift z-40 mt-2 w-60 gap-1 p-2">
                    @foreach ($navLinks as $link)
                        <li>
                            <a href="{{ route($link['route']) }}" wire:navigate @class(['rounded-xl py-3 text-base', 'menu-active' => request()->routeIs($link['route'])])>
                                <x-ui.icon :name="$link['icon']" class="size-5" /> {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
        {{ $slot }}
    </main>

    @include('layouts.partials.site-info')

    @include('layouts.partials.toast')
</body>
</html>
