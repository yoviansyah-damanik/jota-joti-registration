@php
    // Satu kartu = satu lembar B3 vertikal / portrait (353 × 500 mm)
    $sheet = ['width' => 353, 'height' => 500];
    $groups = $participants->pluck('group_number')->filter()->unique()->sort()->values();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="jotajoti">
<head>
    @include('layouts.partials.head', ['title' => __('Participant Cards')])
    <style>
        @page { size: {{ $sheet['width'] }}mm {{ $sheet['height'] }}mm; margin: 0; }

        /* warna latar (pita grup) wajib ikut tercetak */
        * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        /* Kartu dirancang dalam milimeter pada ukuran B3 penuh; di layar diperkecil lewat zoom untuk pratinjau */
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fill, calc({{ $sheet['width'] }}mm * .25)); gap: 1.5rem 1.25rem; justify-content: center; }
        /* Desain color-block modern (mirror ParticipantCardPdf) */
        .b3-card { --a: #16a34a; --t1: #e8f6ec; --t2: #c2e8cf; zoom: .25; position: relative; width: {{ $sheet['width'] }}mm; height: {{ $sheet['height'] - 1 }}mm; box-sizing: border-box; overflow: hidden; background: #f7f7f5; color: #111827; font-family: Helvetica, Arial, sans-serif; }
        .b3-card > * { position: absolute; }
        .b3-card .block { left: 0; top: 0; width: 100%; height: 140mm; background: var(--a); }
        .b3-card .dot-lg { left: 218mm; top: -80mm; width: 200mm; height: 200mm; border-radius: 50%; background: var(--t1); }
        .b3-card .logo-bg { left: 24mm; top: 20mm; width: 56mm; height: 56mm; border-radius: 50%; background: #fff; }
        .b3-card .logo { left: 33mm; top: 29mm; width: 38mm; height: 38mm; object-fit: contain; }
        .b3-card .event { left: 24mm; top: 101mm; width: 190mm; transform: translateY(-50%); font-size: 12mm; font-weight: 700; line-height: 1.15; overflow: hidden; }
        .b3-card .event span { display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .b3-card .event .org { font-size: 9mm; }
        .b3-card .ring { left: 222mm; top: 28mm; width: 100mm; height: 100mm; border-radius: 50%; background: var(--t2); }
        .b3-card .disc { left: 228mm; top: 34mm; width: 88mm; height: 88mm; border-radius: 50%; background: #fff; color: var(--a); display: flex; align-items: center; justify-content: center; font-weight: 700; line-height: 1; }
        .b3-card .name-card { left: 24mm; top: 122mm; width: 305mm; height: 88mm; background: #fff; box-shadow: 0 2.5mm 0 #e4e4e0; display: flex; flex-direction: column; justify-content: center; padding: 0 10mm; box-sizing: border-box; text-align: center; }
        .b3-card .name { font-weight: 700; line-height: 1.08; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .b3-card .troop-no { margin-top: 3mm; font-size: 8.5mm; font-weight: 700; line-height: 1.15; color: var(--a); }
        .b3-card .troop-name { font-size: 7.8mm; line-height: 1.15; color: #6b7280; }
        .b3-card .scan-label { left: 0; top: 235.7mm; width: 100%; text-align: center; font-size: 5.3mm; font-weight: 700; line-height: 1; color: #9ca3af; text-transform: uppercase; }
        .b3-card .qr-frame { left: 80.5mm; top: 250mm; width: 192mm; height: 192mm; background-color: #fff; box-shadow: 0 2.5mm 0 #e4e4e0;
            --col: var(--a); --l: 14mm; --w: 1.8mm; background-image:
            linear-gradient(var(--col), var(--col)), linear-gradient(var(--col), var(--col)), linear-gradient(var(--col), var(--col)), linear-gradient(var(--col), var(--col)),
            linear-gradient(var(--col), var(--col)), linear-gradient(var(--col), var(--col)), linear-gradient(var(--col), var(--col)), linear-gradient(var(--col), var(--col));
            background-repeat: no-repeat;
            background-size: var(--l) var(--w), var(--w) var(--l), var(--l) var(--w), var(--w) var(--l), var(--l) var(--w), var(--w) var(--l), var(--l) var(--w), var(--w) var(--l);
            background-position: 0 0, 0 0, 100% 0, 100% 0, 0 100%, 0 100%, 100% 100%, 100% 100%; }
        .b3-card .qr { left: 90.5mm; top: 260mm; width: 172mm; height: 172mm; }
        .b3-card .qr svg { width: 100%; height: 100%; display: block; }
        .b3-card .code { left: 0; top: 464.4mm; width: 100%; text-align: center; font-family: 'Courier New', monospace; font-size: 12mm; font-weight: 700; line-height: 1; }
        .b3-card .bar { left: 0; bottom: 0; width: 100%; height: 6mm; background: var(--a); }
        @media print {
            body { background: #fff; }
            .cards-page { max-width: none; padding: 0; }
            .cards-grid { display: block; }
            .b3-card { zoom: 1; break-after: page; page-break-after: always; }
            .card-item:last-child .b3-card, .printing-one .card-item.is-printing .b3-card { break-after: auto; page-break-after: auto; }
            /* cetak satu kartu: sembunyikan kartu lain */
            .printing-one .card-item:not(.is-printing) { display: none; }
        }
    </style>
</head>
<body class="bg-base-200">
    <div class="no-print bg-base-100 border-base-300 sticky top-0 z-10 flex flex-wrap items-center justify-between gap-2 border-b px-6 py-3">
        <div>
            <p class="font-medium">{{ __('Participant Cards') }} · {{ $troop->number_label }}</p>
            <p class="text-base-content/60 text-xs">{{ $troop->troop_name }} · {{ __(':count participants', ['count' => $participants->count()]) }}</p>
        </div>
        @if ($participants->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ $pdfUrl() }}" class="btn btn-secondary btn-sm">
                    <x-ui.icon name="download" class="size-4" /> {{ __('Download all (PDF)') }}
                </a>
                <button type="button" onclick="printCards()" class="btn btn-primary btn-sm">
                    <x-ui.icon name="printer" class="size-4" /> {{ __('Print all') }}
                </button>
            </div>
        @endif
    </div>

    <main class="cards-page mx-auto max-w-[1400px] p-6">
        <x-ui.alert type="info" class="no-print mb-4 text-sm">
            {{ __('Each card is printed on its own B3 portrait sheet (:width × :height mm) at 100% scale, so :count sheets are needed. The preview below is shown smaller. The QR code is scanned at the attendance desk.', ['width' => $sheet['width'], 'height' => $sheet['height'], 'count' => $participants->count()]) }}
        </x-ui.alert>

        @if ($groups->isNotEmpty())
            <div class="no-print mb-4 flex flex-wrap items-center gap-2 text-sm">
                <span class="text-base-content/60">{{ __('Group colors') }}:</span>
                @foreach ($groups as $number)
                    <x-ui.group-badge :number="$number" size="md" :label="true" />
                @endforeach
            </div>
        @endif

        <div class="cards-grid">
            @forelse ($participants as $participant)
                @php $color = \App\Support\GroupColor::for($participant->group_number); @endphp
                <div class="card-item flex flex-col gap-2">
                    @php
                        $nameLength = mb_strlen($participant->full_name);
                        $nameSize = $nameLength <= 20 ? 16.2 : ($nameLength <= 32 ? 12.5 : 9.5);
                        $number = $participant->group_number ? (string) $participant->group_number : '-';
                        $numberSize = match (strlen($number)) { 1 => 52.9, 2 => 42.3, default => 31.8 };
                    @endphp
                    <article class="b3-card shadow-md print:shadow-none" style="--a: {{ $color['background'] }}; --t1: {{ \App\Support\GroupColor::tint($participant->group_number, 0.1) }}; --t2: {{ \App\Support\GroupColor::tint($participant->group_number, 0.25) }};">
                        <div class="block"></div>
                        <div class="dot-lg"></div>

                        <div class="logo-bg"></div>
                        <img src="{{ asset('images/logo.webp') }}" alt="" class="logo">
                        <p class="event" style="color: {{ $color['text'] }};"><span>{{ $eventName }}</span><span class="org">{{ $organizationName }}</span></p>

                        <div class="ring"></div>
                        <div class="disc" style="font-size: {{ $numberSize }}mm;">{{ $number }}</div>

                        <div class="name-card">
                            <p class="name" style="font-size: {{ $nameSize }}mm;">{{ $participant->full_name }}</p>
                            <p class="troop-no">{{ $troop->number_label }}</p>
                            <p class="troop-name">{{ $troop->troop_name }}</p>
                        </div>

                        <p class="scan-label">{{ __('Scan at the attendance desk') }}</p>
                        <div class="qr-frame"></div>
                        <div class="qr">{!! \App\Support\QrCode::svg($participant->attendance_code, 512, 0) !!}</div>
                        <p class="code">{{ $participant->attendance_code }}</p>

                        <div class="bar"></div>
                    </article>

                    {{-- aksi per kartu (tidak ikut tercetak) --}}
                    <div class="no-print flex items-center justify-between gap-2" style="width: calc({{ $sheet['width'] }}mm * .25);">
                        <p class="min-w-0 truncate text-xs font-medium" title="{{ $participant->full_name }}">{{ $participant->full_name }}</p>
                        <div class="join shrink-0">
                            <button type="button" class="btn btn-xs btn-soft join-item" onclick="printCard(this)" title="{{ __('Print this card') }}">
                                <x-ui.icon name="printer" class="size-3.5" /> {{ __('Print') }}
                            </button>
                            <a href="{{ $pdfUrl($participant) }}" class="btn btn-xs btn-soft join-item" title="{{ __('Download PDF') }}">
                                <x-ui.icon name="download" class="size-3.5" /> PDF
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full"><x-ui.empty-state icon="qr" :title="__('No participant IDs yet')" :description="__('IDs are issued when the registration is verified.')" /></div>
            @endforelse
        </div>
    </main>

    <script>
        // Cetak satu kartu: kartu lain disembunyikan lewat CSS cetak, lalu dipulihkan setelah dialog cetak ditutup
        function printCard(button) {
            document.body.classList.add('printing-one');
            button.closest('.card-item').classList.add('is-printing');
            window.print();
        }

        function printCards() {
            window.print();
        }

        window.addEventListener('afterprint', () => {
            document.body.classList.remove('printing-one');
            document.querySelectorAll('.is-printing').forEach((el) => el.classList.remove('is-printing'));
        });
    </script>
</body>
</html>
