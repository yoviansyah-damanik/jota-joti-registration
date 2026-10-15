@php
    $menu = [
        ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'home', 'label' => __('Dashboard')],
        ['route' => 'admin.registrations.index', 'match' => 'admin.registrations.*', 'icon' => 'clipboard-check', 'label' => __('Registrations')],
        ['route' => 'admin.reports.payments', 'match' => 'admin.reports.*', 'icon' => 'banknotes', 'label' => __('Fee Report')],
        ['route' => 'admin.groups', 'match' => 'admin.groups*', 'icon' => 'user-group', 'label' => __('Group Numbers')],
        ['route' => 'admin.attendance.index', 'match' => 'admin.attendance.index', 'icon' => 'check-circle', 'label' => __('Attendance')],
        ['route' => 'admin.attendance.sessions', 'match' => 'admin.attendance.sessions', 'icon' => 'table', 'label' => __('Attendance Sessions')],
        ['route' => 'admin.tasks.index', 'match' => 'admin.tasks.*', 'icon' => 'document', 'label' => __('Penilaian Tugas')],
        ['route' => 'admin.certificates.settings', 'match' => 'admin.certificates.settings', 'icon' => 'award', 'label' => __('Sertifikat')],
        ['route' => 'admin.settings', 'match' => 'admin.settings', 'icon' => 'cog', 'label' => __('Settings')],
    ];
    $user = auth()->user();
@endphp

<aside class="bg-base-100 shadow-soft flex min-h-full w-64 flex-col">
    <div class="flex items-center gap-3 px-5 pb-4 pt-6">
        <x-ui.logo class="size-14" />
        <div class="min-w-0">
            <p class="text-sm font-extrabold leading-tight">{{ $eventName }}</p>
            <p class="text-base-content/60 text-xs leading-tight">{{ $organizationName }}</p>
            <div class="mt-0.5 flex items-center gap-1.5">
                <span class="text-primary text-xs font-semibold">{{ __('Admin') }}</span>
                <span class="badge badge-ghost badge-xs font-mono font-medium opacity-70">v{{ config('app.version') }}</span>
            </div>
        </div>
    </div>

    <ul class="menu w-full flex-1 gap-1 px-3">
        @foreach ($menu as $item)
            <li>
                <a href="{{ route($item['route']) }}" wire:navigate @class(['rounded-xl py-2.5', 'menu-active' => request()->routeIs($item['match'])])>
                    <x-ui.icon :name="$item['icon']" /> {{ $item['label'] }}
                </a>
            </li>
        @endforeach
    </ul>

    <div class="p-3">
        <div @class(['bg-base-200 flex items-center gap-2 rounded-xl p-2.5 transition', 'ring-2 ring-primary/80 bg-primary/10' => request()->routeIs('admin.profile')])>
            <a href="{{ route('admin.profile') }}" wire:navigate class="flex items-center gap-2.5 min-w-0 flex-1 hover:opacity-80 transition-opacity" title="{{ __('Edit Profile') }}">
                <span class="bg-primary text-primary-content flex size-9 shrink-0 items-center justify-center rounded-full text-sm font-bold">
                    {{ \Illuminate\Support\Str::of($user?->name)->substr(0, 1)->upper() }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold">{{ $user?->name }}</p>
                    <p class="text-base-content/60 truncate text-xs">{{ $user?->username ? '@'.$user->username : $user?->email }}</p>
                </div>
            </a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm btn-square" title="{{ __('Log out') }}" aria-label="{{ __('Log out') }}">
                    <x-ui.icon name="logout" />
                </button>
            </form>
        </div>
    </div>
</aside>
