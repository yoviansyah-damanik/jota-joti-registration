<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="jotajoti">
<head>
    @include('layouts.partials.head', ['title' => __('Attendance Sheet')])
    <style>
        @page { size: A4; margin: 14mm; }
        @media print { .sheet { break-after: page; } .sheet:last-child { break-after: auto; } body { background: #fff; } }
        .sheet table td, .sheet table th { border: 1px solid #999; padding: 4px 6px; }
    </style>
</head>
<body class="bg-base-200 text-sm">
    <div class="no-print bg-base-100 border-base-300 sticky top-0 flex items-center justify-between border-b px-6 py-3">
        <span class="font-medium">{{ __('Attendance Sheet') }} · {{ $session->name }}</span>
        <button type="button" onclick="window.print()" class="btn btn-primary btn-sm">
            <x-ui.icon name="printer" class="size-4" /> {{ __('Print') }}
        </button>
    </div>

    @forelse ($troops as $participants)
        @php
            $troop = $participants->first()->troop;
            $isPresent = fn ($p) => $p->attendance?->status === \App\Enums\AttendanceStatus::Present;
            $presentMale = $participants->filter(fn ($p) => $isPresent($p) && $p->gender === \App\Enums\Gender::Male)->count();
            $presentFemale = $participants->filter(fn ($p) => $isPresent($p) && $p->gender === \App\Enums\Gender::Female)->count();
        @endphp
        <section class="sheet bg-base-100 mx-auto my-6 max-w-[210mm] p-8 print:m-0 print:p-0">
            <header class="mb-4 flex items-center gap-4 border-b pb-3">
                <x-ui.logo class="size-20" />
                <div class="flex-1 text-center">
                    <h1 class="text-lg font-bold uppercase">{{ __('Attendance Sheet') }}</h1>
                    <p class="font-semibold">{{ $eventName }}</p>
                    <p>{{ $organizationName }}</p>
                    <p>{{ $session->name }} · {{ $session->date->translatedFormat('l, d F Y') }}</p>
                </div>
                <div class="size-20"></div>
            </header>

            <table class="mb-4">
                <tr><td class="border-0! py-0!">{{ __('Troop number') }}</td><td class="border-0! py-0!">: {{ $troop->number_label }}</td></tr>
                <tr><td class="border-0! py-0!">{{ __('Troop name') }}</td><td class="border-0! py-0!">: {{ $troop->troop_name }}</td></tr>
                <tr><td class="border-0! py-0!">{{ __('Unit') }}</td><td class="border-0! py-0!">: {{ $troop->unit?->label() }}</td></tr>
            </table>

            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-base-200">
                        <th class="w-8">No</th>
                        <th>{{ __('KTA number') }}</th>
                        <th>{{ __('Full name') }}</th>
                        <th class="w-10">L/P</th>
                        <th class="w-14">{{ __('Group') }}</th>
                        <th class="w-20">{{ __('Status') }}</th>
                        <th class="w-32">{{ __('Signature') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($participants as $participant)
                        <tr class="h-9">
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="font-mono">{{ $participant->kta_number }}</td>
                            <td>{{ $participant->full_name }}</td>
                            <td class="text-center">{{ $participant->gender->value }}</td>
                            <td class="text-center"><x-ui.group-badge :number="$participant->group_number" /></td>
                            <td class="text-center">{{ $participant->attendance?->status->label() }}</td>
                            <td @class(['text-xs' => true, 'pl-12' => $loop->even])>{{ $loop->iteration }}.</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="mt-4 w-56">
                <tr><td>{{ __('Present') }} {{ __('Male') }}</td><td class="w-16 text-center">{{ $presentMale }}</td></tr>
                <tr><td>{{ __('Present') }} {{ __('Female') }}</td><td class="text-center">{{ $presentFemale }}</td></tr>
                <tr class="font-semibold"><td>{{ __('Total present') }}</td><td class="text-center">{{ $presentMale + $presentFemale }}</td></tr>
            </table>

            <div class="mt-10 flex justify-end">
                <div class="w-56 text-center">
                    <p>{{ __('Troop leader') }}</p>
                    <div class="h-20"></div>
                    <p class="border-t border-black pt-1">&nbsp;</p>
                </div>
            </div>
        </section>
    @empty
        <div class="p-10"><x-ui.empty-state icon="users" :title="__('No participants found')" /></div>
    @endforelse
</body>
</html>
