<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="jotajoti">
<head>
    @include('layouts.partials.head', ['title' => __('Manual Attendance Sheet')])
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }
        @media print {
            .no-print { display: none !important; }
            html, body {
                background: #fff !important;
                color: #000 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .sheet {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                min-height: 272mm !important;
                max-height: 275mm !important;
                break-after: page;
                page-break-after: always;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
            }
            .sheet:last-child {
                break-after: auto;
                page-break-after: auto;
            }
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        .sheet table td, .sheet table th {
            border: 1px solid #111;
            padding: 3px 5px;
        }
    </style>
</head>
<body class="bg-base-200 text-sm antialiased text-black">
    {{-- Control Toolbar (No-Print) --}}
    <header class="no-print bg-base-100 border-base-300 sticky top-0 z-50 border-b px-4 py-3 shadow-sm">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-ghost btn-sm">
                    <x-ui.icon name="arrow-left" class="size-4" /> {{ __('Back') }}
                </a>
                <span class="font-bold">{{ __('Manual Attendance Print') }}</span>
                <span class="badge badge-primary badge-sm font-semibold">
                    {{ $groupBy === 'troop' ? __('Per Troop (1 Troop / Page)') : __('Per Group Number') }}
                </span>
            </div>

            {{-- Filter Bar --}}
            <form method="GET" action="{{ route('admin.print.attendance.manual') }}" class="flex flex-wrap items-center gap-2 text-xs">
                {{-- Mode Pengelompokan --}}
                <select name="group_by" onchange="this.form.submit()" class="select select-bordered select-xs font-semibold">
                    <option value="troop" @selected($groupBy === 'troop')>{{ __('Per Troop (Gudep)') }}</option>
                    <option value="group" @selected($groupBy === 'group')>{{ __('Per Group Number (Grup)') }}</option>
                </select>

                {{-- Jumlah Baris per Lembar --}}
                <select name="rows_per_page" onchange="this.form.submit()" class="select select-bordered select-xs" title="{{ __('Rows per sheet') }}">
                    <option value="15" @selected($rowsPerPage == 15)>15 {{ __('Rows/Sheet') }}</option>
                    <option value="20" @selected($rowsPerPage == 20)>20 {{ __('Rows/Sheet (Full A4)') }}</option>
                    <option value="25" @selected($rowsPerPage == 25)>25 {{ __('Rows/Sheet (Dense)') }}</option>
                </select>

                {{-- Filter Golongan --}}
                <select name="unit" onchange="this.form.submit()" class="select select-bordered select-xs">
                    <option value="">{{ __('All Units') }}</option>
                    <option value="{{ \App\Enums\ScoutUnit::Penggalang->value }}" @selected($unit === \App\Enums\ScoutUnit::Penggalang->value)>
                        {{ \App\Enums\ScoutUnit::Penggalang->label() }}
                    </option>
                    <option value="{{ \App\Enums\ScoutUnit::PenegakPandega->value }}" @selected($unit === \App\Enums\ScoutUnit::PenegakPandega->value)>
                        {{ \App\Enums\ScoutUnit::PenegakPandega->label() }}
                    </option>
                </select>

                {{-- Filter Pilihan Gudep atau Grup --}}
                @if ($groupBy === 'troop')
                    <select name="troop_id" onchange="this.form.submit()" class="select select-bordered select-xs max-w-xs">
                        <option value="">{{ __('All Troops') }} ({{ count($allTroops) }} gudep)</option>
                        @foreach ($allTroops as $t)
                            <option value="{{ $t->id }}" @selected($troopId === (string) $t->id)>
                                {{ $t->number_label }} – {{ $t->troop_name }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <select name="group_number" onchange="this.form.submit()" class="select select-bordered select-xs">
                        <option value="">{{ __('All Groups') }}</option>
                        <optgroup label="{{ __('Penggalang Block') }}">
                            @foreach ($penggalangGroups as $g)
                                <option value="{{ $g }}" @selected((string) $groupNumber === (string) $g)>{{ __('Group') }} {{ $g }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="{{ __('Penegak Block') }}">
                            @foreach ($penegakGroups as $g)
                                <option value="{{ $g }}" @selected((string) $groupNumber === (string) $g)>{{ __('Group') }} {{ $g }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                @endif

                {{-- Filter Sesi --}}
                <select name="session_id" onchange="this.form.submit()" class="select select-bordered select-xs">
                    <option value="">{{ __('General Format') }}</option>
                    @foreach ($sessions as $s)
                        <option value="{{ $s->id }}" @selected($sessionId == $s->id)>
                            {{ $s->name }} ({{ $s->date->format('d/m') }})
                        </option>
                    @endforeach
                </select>

                <button type="button" onclick="window.print()" class="btn btn-primary btn-xs gap-1.5 ml-1">
                    <x-ui.icon name="printer" class="size-3.5" /> {{ __('Print') }}
                </button>
            </form>
        </div>
    </header>

    {{-- Printable Sheets --}}
    @forelse ($pages as $page)
        @php
            $isTroop = $page['type'] === 'troop';
            $troop = $page['troop'] ?? null;
            $unitEnum = $page['unit'] ?? null;
            $groupNum = $page['group_number'] ?? null;
        @endphp

        <section class="sheet bg-base-100 shadow-soft mx-auto my-6 max-w-[210mm] min-h-[272mm] flex flex-col justify-between p-6 print:m-0 print:p-0">
            <div>
                {{-- KOP Surat Resmi --}}
                <header class="mb-3 flex items-center gap-3 border-b-2 border-black pb-2">
                    <x-ui.logo class="size-16 shrink-0 object-contain" />
                    <div class="flex-1 text-center">
                        <h1 class="text-base font-extrabold uppercase tracking-wide">{{ __('MANUAL ATTENDANCE SHEET') }}</h1>
                        <p class="text-xs font-bold uppercase">{{ $eventName }}</p>
                        <p class="text-[11px] font-semibold">{{ $organizationName }}</p>
                        <p class="text-[11px] text-base-content/80 font-medium">
                            @if ($session)
                                {{ __('Session') }}: <span class="font-bold">{{ $session->name }}</span> · {{ $session->date->translatedFormat('l, d F Y') }}
                            @else
                                {{ __('Activity Checkpoint / Operating Station Attendance') }}
                            @endif
                        </p>
                    </div>
                    <div class="size-16 shrink-0"></div>
                </header>

                {{-- Metadata Box --}}
                <div class="mb-3 grid grid-cols-2 gap-x-4 gap-y-1 rounded-sm border border-black bg-base-200/40 p-2 text-xs">
                    @if ($isTroop)
                        <div>
                            <span class="font-semibold">{{ __('Troop') }}:</span>
                            <span class="font-bold ml-1">{{ $troop?->number_label }} – {{ $troop?->troop_name }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-semibold">{{ __('Unit / Golongan') }}:</span>
                            <span class="font-bold ml-1 uppercase">{{ $unitEnum?->label() ?? '-' }}</span>
                            @if ($page['total_pages'] > 1)
                                <span class="border border-black px-1.5 py-0.2 rounded-xs ml-2 font-mono font-bold text-[10px]">
                                    Hal {{ $page['page_number'] }} / {{ $page['total_pages'] }}
                                </span>
                            @endif
                        </div>
                        <div>
                            <span class="font-semibold">{{ __('Troop Leader') }}:</span>
                            <span class="ml-1">{{ $troop?->leader_name }} ({{ $troop?->phone }})</span>
                        </div>
                        <div class="text-right">
                            <span class="font-semibold">{{ __('Registered') }}:</span>
                            <span class="ml-1 font-semibold">{{ $page['total_participants'] }} {{ __('participants') }} ({{ $page['male_count'] }} L / {{ $page['female_count'] }} P)</span>
                        </div>
                    @else
                        <div>
                            <span class="font-semibold">{{ __('Group') }}:</span>
                            <span class="font-extrabold text-sm ml-1">{{ __('GROUP :num', ['num' => $groupNum]) }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-semibold">{{ __('Unit / Golongan') }}:</span>
                            <span class="font-bold ml-1 uppercase">{{ $unitEnum?->label() ?? '-' }}</span>
                            @if ($page['total_pages'] > 1)
                                <span class="border border-black px-1.5 py-0.2 rounded-xs ml-2 font-mono font-bold text-[10px]">
                                    Hal {{ $page['page_number'] }} / {{ $page['total_pages'] }}
                                </span>
                            @endif
                        </div>
                        <div>
                            <span class="font-semibold">{{ __('Station / Checkpoint') }}:</span>
                            <span class="border-b border-dotted border-black inline-block w-40 ml-1">&nbsp;</span>
                        </div>
                        <div class="text-right">
                            <span class="font-semibold">{{ __('Participants') }}:</span>
                            <span class="ml-1 font-semibold">{{ $page['total_participants'] }} {{ __('participants') }} ({{ $page['male_count'] }} L / {{ $page['female_count'] }} P)</span>
                        </div>
                    @endif
                </div>

                {{-- Tabel Peserta Full Lembar --}}
                <table class="w-full border-collapse text-xs">
                    <thead>
                        <tr class="bg-base-200/80 font-bold uppercase text-[11px]">
                            <th class="w-8 text-center py-1">No</th>
                            <th class="w-24 text-center py-1">{{ __('Attendance ID') }}</th>
                            <th class="text-left py-1">{{ __('Full Name') }}</th>
                            <th class="w-9 text-center py-1">L/P</th>
                            @if ($isTroop)
                                <th class="w-16 text-center py-1">{{ __('Group') }}</th>
                            @else
                                <th class="w-44 text-left py-1">{{ __('Troop') }}</th>
                            @endif
                            <th class="w-44 text-left py-1">{{ __('Signature / Paraf') }}</th>
                            <th class="w-20 text-center py-1">{{ __('Notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Baris Peserta Terisi --}}
                        @foreach ($page['participants'] as $idx => $participant)
                            @php
                                $rowNumber = $page['start_index'] + $idx;
                            @endphp
                            <tr class="h-7.5">
                                <td class="text-center font-medium">{{ $rowNumber }}</td>
                                <td class="font-mono text-center font-semibold text-[11px]">{{ $participant->attendance_code }}</td>
                                <td class="font-semibold">{{ $participant->full_name }}</td>
                                <td class="text-center">{{ $participant->gender->value }}</td>
                                @if ($isTroop)
                                    <td class="text-center font-bold">
                                        {{ $participant->group_number ? __('Grup') . ' ' . $participant->group_number : '-' }}
                                    </td>
                                @else
                                    <td class="text-[11px] leading-tight">
                                        <span class="font-bold">{{ $participant->troop->number_label }}</span>
                                        <span class="text-base-content/70 block truncate max-w-[170px]">{{ $participant->troop->troop_name }}</span>
                                    </td>
                                @endif
                                <td class="align-bottom pb-1 text-[11px]">
                                    @if ($rowNumber % 2 !== 0)
                                        <span class="inline-block w-5 font-bold">{{ $rowNumber }}.</span>
                                        <span class="border-b border-dotted border-black/60 inline-block w-28">&nbsp;</span>
                                    @else
                                        <span class="inline-block w-14"></span>
                                        <span class="inline-block w-5 font-bold">{{ $rowNumber }}.</span>
                                        <span class="border-b border-dotted border-black/60 inline-block w-20">&nbsp;</span>
                                    @endif
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                        @endforeach

                        {{-- Baris Kosong Pengisi Sampai Penuh Lembar --}}
                        @for ($e = 1; $e <= $page['empty_rows_count']; $e++)
                            @php
                                $rowNumber = $page['start_index'] + $page['participants']->count() + $e - 1;
                            @endphp
                            <tr class="h-7.5">
                                <td class="text-center font-medium text-black">{{ $rowNumber }}</td>
                                <td class="text-center">&nbsp;</td>
                                <td>&nbsp;</td>
                                <td class="text-center">&nbsp;</td>
                                <td>&nbsp;</td>
                                <td class="align-bottom pb-1 text-[11px] text-black">
                                    @if ($rowNumber % 2 !== 0)
                                        <span class="inline-block w-5 font-bold">{{ $rowNumber }}.</span>
                                        <span class="border-b border-dotted border-black/40 inline-block w-28">&nbsp;</span>
                                    @else
                                        <span class="inline-block w-14"></span>
                                        <span class="inline-block w-5 font-bold">{{ $rowNumber }}.</span>
                                        <span class="border-b border-dotted border-black/40 inline-block w-20">&nbsp;</span>
                                    @endif
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {{-- Rekapitulasi & Tanda Tangan Pengesahan (Selalu di Dasar Lembar) --}}
            <div class="mt-4 grid grid-cols-2 gap-4 text-xs pt-1">
                <div class="rounded-sm border border-black p-2.5">
                    <p class="font-bold uppercase tracking-wider mb-1 text-[11px]">{{ __('Attendance Recap') }}</p>
                    <table class="w-full text-xs">
                        <tr>
                            <td class="border-0! py-0.5!">{{ __('Total Participants') }}</td>
                            <td class="border-0! py-0.5! font-bold">: {{ $page['total_participants'] }} {{ __('people') }}</td>
                        </tr>
                        <tr>
                            <td class="border-0! py-0.5!">{{ __('Present') }}</td>
                            <td class="border-0! py-0.5!">: [ _____ ] L &nbsp; [ _____ ] P &nbsp; = &nbsp; [ _____ ] {{ __('people') }}</td>
                        </tr>
                        <tr>
                            <td class="border-0! py-0.5!">{{ __('Absent / Excused') }}</td>
                            <td class="border-0! py-0.5!">: [ _____ ] {{ __('people') }}</td>
                        </tr>
                    </table>
                </div>

                <div class="text-center flex flex-col justify-between p-1">
                    <p class="text-[11px]">
                        {{ $settings->get('certificate_city') ?: 'Kota Pelaksana' }},
                        ..................................... {{ now()->year }}
                    </p>
                    <p class="font-semibold text-xs -mt-1">
                        @if ($isTroop)
                            {{ __('Troop Leader / Officer in Charge') }}
                        @else
                            {{ __('Station Instructor / Checkpoint Officer') }}
                        @endif
                    </p>
                    <div class="h-12"></div>
                    <div>
                        <p class="font-bold underline text-xs">( ............................................................................ )</p>
                        <p class="text-[10px] text-base-content/70 mt-0.5">NTA / Call Sign: ............................................</p>
                    </div>
                </div>
            </div>
        </section>
    @empty
        <div class="sheet bg-base-100 shadow-soft mx-auto my-12 max-w-md p-10 text-center">
            <x-ui.empty-state icon="users" :title="__('No participants found matching the selected filters.')" />
        </div>
    @endforelse
</body>
</html>
