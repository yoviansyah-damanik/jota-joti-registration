@php
    $rupiah = fn (int $amount) => \App\Support\Rupiah::format($amount);
    $periodLabel = match (true) {
        $filters['from'] && $filters['to'] => \Carbon\Carbon::parse($filters['from'])->translatedFormat('d F Y').' – '.\Carbon\Carbon::parse($filters['to'])->translatedFormat('d F Y'),
        (bool) $filters['from'] => __('From :date', ['date' => \Carbon\Carbon::parse($filters['from'])->translatedFormat('d F Y')]),
        (bool) $filters['to'] => __('Until :date', ['date' => \Carbon\Carbon::parse($filters['to'])->translatedFormat('d F Y')]),
        default => __('All periods'),
    };
    $received = $troops->where('status', \App\Enums\TroopStatus::Verified);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="jotajoti">
<head>
    @include('layouts.partials.head', ['title' => __('Registration Fee Receipt Report')])
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        @media print { body { background: #fff; } .sheet { box-shadow: none !important; margin: 0 !important; padding: 0 !important; } }
        .sheet table td, .sheet table th { border: 1px solid #999; padding: 4px 6px; }
    </style>
</head>
<body class="bg-base-200 text-sm">
    <div class="no-print bg-base-100 border-base-300 sticky top-0 flex items-center justify-between border-b px-6 py-3">
        <span class="font-medium">{{ __('Registration Fee Receipt Report') }}</span>
        <button type="button" onclick="window.print()" class="btn btn-primary btn-sm">
            <x-ui.icon name="printer" class="size-4" /> {{ __('Print') }}
        </button>
    </div>

    <section class="sheet bg-base-100 shadow-soft mx-auto my-6 max-w-[297mm] p-8">
        <header class="mb-4 flex items-center gap-4 border-b pb-3">
            <x-ui.logo class="size-20" />
            <div class="flex-1 text-center">
                <h1 class="text-lg font-bold uppercase">{{ __('Registration Fee Receipt Report') }}</h1>
                <p class="font-semibold">{{ $eventName }}</p>
                <p>{{ $organizationName }}</p>
                <p>{{ __('Period') }}: {{ $periodLabel }}</p>
            </div>
            <div class="size-20"></div>
        </header>

        <table class="mb-4 w-full">
            <thead>
                <tr class="bg-base-200">
                    <th>{{ __('No') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th class="text-left">{{ __('Troop number') }}</th>
                    <th class="text-left">{{ __('Troop name') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-right">{{ __('Participants') }}</th>
                    <th class="text-right">{{ __('Fee per participant') }}</th>
                    <th class="text-right">{{ __('Amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($troops as $troop)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="whitespace-nowrap text-center">{{ ($troop->verified_at ?? $troop->submitted_at)?->format('d/m/Y') }}</td>
                        <td class="whitespace-nowrap font-mono">{{ $troop->number_label }}</td>
                        <td>{{ $troop->troop_name }}</td>
                        <td class="text-center">{{ $troop->status->label() }}</td>
                        <td class="text-right tabular-nums">{{ $troop->participants_count }}</td>
                        <td class="whitespace-nowrap text-right tabular-nums">{{ $rupiah($troop->fee_per_participant) }}</td>
                        <td class="whitespace-nowrap text-right font-semibold tabular-nums">{{ $rupiah($troop->fee_total) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-6 text-center">{{ __('No fee data for this filter') }}</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="font-bold">
                    <td colspan="7" class="text-right">{{ __('Total received') }}</td>
                    <td class="whitespace-nowrap text-right tabular-nums">{{ $rupiah($summary['received']) }}</td>
                </tr>
                <tr>
                    <td colspan="7" class="text-right">{{ __('Total awaiting verification') }}</td>
                    <td class="whitespace-nowrap text-right tabular-nums">{{ $rupiah($summary['pending']) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="mt-8 flex justify-end">
            <div class="w-64 text-center">
                <p>{{ __('Printed on :date', ['date' => now()->translatedFormat('d F Y')]) }}</p>
                <p class="mb-16">{{ __('Treasurer') }}</p>
                <p class="border-t pt-1">(................................)</p>
            </div>
        </div>
    </section>
</body>
</html>
