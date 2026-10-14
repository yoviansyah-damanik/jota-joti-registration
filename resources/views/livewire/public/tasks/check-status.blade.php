<div class="mx-auto max-w-4xl space-y-8">
    {{-- Header --}}
    <div class="text-center space-y-2">
        <span class="badge badge-primary badge-outline font-semibold tracking-wider uppercase text-xs">JOTA-JOTI Assessment</span>
        <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl text-base-content">
            {{ __('Cek Penilaian Tugas') }}
        </h1>
        <p class="text-base-content/70 mx-auto max-w-xl text-sm sm:text-base">
            {{ __('Pantau status penilaian dan evaluasi tugas JOTA Digital serta JOTI Anda secara langsung menggunakan ID Peserta.') }}
        </p>
    </div>

    {{-- Search Bar Card --}}
    <div class="card bg-base-100 shadow-soft rounded-3xl p-6 sm:p-8 border border-base-200/80 max-w-2xl mx-auto">
        <form wire:submit="check" class="space-y-4">
            <label class="label pb-1" for="search_code">
                <span class="label-text font-bold text-sm text-base-content">{{ __('Masukkan ID Peserta') }}</span>
                <span class="label-text-alt text-base-content/60">{{ __('Contoh: P-ABC12345') }}</span>
            </label>
            <div class="join w-full">
                <input
                    type="text"
                    id="search_code"
                    wire:model="code"
                    placeholder="Contoh: P-A1B2C3D4"
                    class="input input-bordered join-item w-full uppercase font-mono tracking-wider focus:outline-primary @error('code') input-error @enderror"
                />
                <button type="submit" class="btn btn-primary join-item px-6" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="check">
                        <x-ui.icon name="search" class="size-4" />
                    </span>
                    <span wire:loading wire:target="check" class="loading loading-spinner loading-xs"></span>
                    {{ __('Periksa') }}
                </button>
            </div>
            @error('code')
                <p class="text-error text-xs font-medium mt-1 flex items-center gap-1">
                    <x-ui.icon name="exclamation" class="size-3.5 shrink-0" /> {{ $message }}
                </p>
            @enderror
        </form>
    </div>

    {{-- Result Section --}}
    @if ($searched && $participant)
        <div class="space-y-6 animate-fadeIn">
            {{-- Profile Card --}}
            <div class="card bg-base-100 shadow-lift rounded-3xl p-6 border border-base-200 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content rounded-2xl size-14 font-extrabold text-xl shadow-md">
                            {{ \Illuminate\Support\Str::of($participant->full_name)->substr(0, 1)->upper() }}
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg sm:text-xl font-extrabold text-base-content">{{ $participant->full_name }}</h2>
                            <span class="badge badge-sm badge-ghost font-mono font-bold">{{ $participant->attendance_code }}</span>
                        </div>
                        <p class="text-sm text-base-content/70">
                            {{ $participant->troop?->troop_name }} ({{ $participant->troop?->troop_number }}) · {{ $participant->troop?->unit?->label() }}
                        </p>
                    </div>
                </div>

                @if ($submission)
                    <div class="flex flex-col items-center sm:items-end w-full md:w-auto p-3 bg-base-200/50 rounded-2xl">
                        <span class="text-xs text-base-content/60 font-medium">{{ __('Status Akhir Penilaian') }}</span>
                        <div class="mt-1">
                            <span @class([
                                'badge badge-lg font-bold gap-1.5 px-3 py-3.5 text-xs uppercase tracking-wide',
                                $submission->final_status->badgeClass(),
                            ])>
                                @if ($submission->final_status === \App\Enums\TaskStatus::Completed)
                                    <x-ui.icon name="check-circle" class="size-4" />
                                @elseif ($submission->final_status === \App\Enums\TaskStatus::Revision)
                                    <x-ui.icon name="exclamation" class="size-4" />
                                @elseif ($submission->final_status === \App\Enums\TaskStatus::Rejected)
                                    <x-ui.icon name="x-circle" class="size-4" />
                                @else
                                    <x-ui.icon name="info" class="size-4" />
                                @endif
                                {{ $submission->final_status->label() }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Jika Belum Mengirimkan Tugas --}}
            @if (! $submission)
                <div class="card bg-base-100 shadow-soft rounded-3xl p-8 sm:p-12 text-center space-y-4 border border-dashed border-base-300">
                    <div class="size-16 rounded-full bg-warning/10 text-warning mx-auto flex items-center justify-center">
                        <x-ui.icon name="document" class="size-8" />
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-lg font-bold text-base-content">{{ __('Belum Ada Pengiriman Tugas') }}</h3>
                        <p class="text-sm text-base-content/60 max-w-md mx-auto">
                            {{ __('Peserta dengan ID ini belum mengirimkan laporan tugas JOTA Digital dan JOTI.') }}
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('tasks.submit', ['code' => $participant->attendance_code]) }}" class="btn btn-primary font-bold shadow-md">
                            <x-ui.icon name="upload" class="size-4" /> {{ __('Kirimkan Tugas Sekarang') }}
                        </a>
                    </div>
                </div>
            @else
                {{-- Detail Penilaian Dua Tugas --}}
                <div class="grid gap-6 md:grid-cols-2">
                    {{-- Card JOTA Digital --}}
                    <div class="card bg-base-100 shadow-soft rounded-3xl p-6 border border-base-200/90 space-y-4">
                        <div class="flex items-start justify-between gap-3 pb-3 border-b border-base-200">
                            <div>
                                <span class="badge badge-primary badge-sm font-semibold mb-1">Radio / JOTA</span>
                                <h3 class="font-extrabold text-base text-base-content">{{ __('Tugas JOTA Digital') }}</h3>
                                <p class="text-xs text-base-content/60 font-mono">JID: <span class="font-bold text-base-content">{{ $submission->jid }}</span></p>
                            </div>
                            <span @class(['badge font-bold text-xs', $submission->jota_status->badgeClass()])>
                                {{ $submission->jota_status->label() }}
                            </span>
                        </div>

                        <div class="space-y-2 text-sm">
                            <span class="text-xs font-semibold text-base-content/60 uppercase tracking-wider">{{ __('Catatan Reviewer') }}:</span>
                            <div class="bg-base-200/50 rounded-xl p-3 min-h-[60px] text-xs leading-relaxed text-base-content/80">
                                {{ $submission->jota_admin_note ?: __('Belum ada catatan khusus untuk tugas ini.') }}
                            </div>
                        </div>

                        <div class="pt-2">
                            <a
                                href="{{ route('tasks.download', ['submission' => $submission->id, 'type' => 'jota', 'code' => $participant->attendance_code, 'inline' => 1]) }}"
                                target="_blank"
                                class="btn btn-outline btn-primary btn-sm w-full gap-2"
                            >
                                <x-ui.icon name="document" class="size-4" /> {{ __('Lihat Berkas Tugas JOTA (PDF)') }}
                            </a>
                        </div>
                    </div>

                    {{-- Card JOTI --}}
                    <div class="card bg-base-100 shadow-soft rounded-3xl p-6 border border-base-200/90 space-y-4">
                        <div class="flex items-start justify-between gap-3 pb-3 border-b border-base-200">
                            <div>
                                <span class="badge badge-accent badge-sm font-semibold mb-1">Internet / JOTI</span>
                                <h3 class="font-extrabold text-base text-base-content">{{ __('Tugas JOTI') }}</h3>
                                <p class="text-xs text-base-content/60 font-mono">JJID: <span class="font-bold text-base-content">{{ $submission->jjid }}</span></p>
                            </div>
                            <span @class(['badge font-bold text-xs', $submission->joti_status->badgeClass()])>
                                {{ $submission->joti_status->label() }}
                            </span>
                        </div>

                        <div class="space-y-2 text-sm">
                            <span class="text-xs font-semibold text-base-content/60 uppercase tracking-wider">{{ __('Catatan Reviewer') }}:</span>
                            <div class="bg-base-200/50 rounded-xl p-3 min-h-[60px] text-xs leading-relaxed text-base-content/80">
                                {{ $submission->joti_admin_note ?: __('Belum ada catatan khusus untuk tugas ini.') }}
                            </div>
                        </div>

                        <div class="pt-2">
                            <a
                                href="{{ route('tasks.download', ['submission' => $submission->id, 'type' => 'joti', 'code' => $participant->attendance_code, 'inline' => 1]) }}"
                                target="_blank"
                                class="btn btn-outline btn-accent btn-sm w-full gap-2"
                            >
                                <x-ui.icon name="document" class="size-4" /> {{ __('Lihat Berkas Tugas JOTI (PDF)') }}
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Catatan Evaluasi Akhir / Admin Notes --}}
                @if ($submission->admin_notes)
                    <div class="alert bg-base-100 shadow-soft border border-primary/20 rounded-2xl p-4">
                        <x-ui.icon name="info" class="size-5 text-primary shrink-0" />
                        <div>
                            <h4 class="font-bold text-sm text-base-content">{{ __('Catatan Evaluasi Panitia') }}:</h4>
                            <p class="text-xs text-base-content/80 mt-0.5 leading-relaxed">{{ $submission->admin_notes }}</p>
                        </div>
                    </div>
                @endif

                {{-- Action jika butuh revisi / perbaikan --}}
                @if (in_array($submission->final_status, [\App\Enums\TaskStatus::Revision, \App\Enums\TaskStatus::Rejected], true) ||
                     in_array($submission->jota_status, [\App\Enums\TaskStatus::Revision, \App\Enums\TaskStatus::Rejected], true) ||
                     in_array($submission->joti_status, [\App\Enums\TaskStatus::Revision, \App\Enums\TaskStatus::Rejected], true))
                    <div class="bg-warning/10 border border-warning/30 rounded-3xl p-6 text-center space-y-3">
                        <h4 class="font-extrabold text-base text-warning-content">{{ __('Tugas Perlu Diperbaiki / Diunggah Ulang') }}</h4>
                        <p class="text-xs text-base-content/70 max-w-lg mx-auto">
                            {{ __('Silakan perbaiki berkas tugas sesuai catatan reviewer di atas, kemudian kirimkan pembaruan berkas melalui tombol di bawah ini.') }}
                        </p>
                        <div>
                            <a href="{{ route('tasks.submit', ['code' => $participant->attendance_code]) }}" class="btn btn-warning font-bold gap-2">
                                <x-ui.icon name="upload" class="size-4" /> {{ __('Perbaiki / Unggah Ulang Berkas Tugas') }}
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Meta Information --}}
                <div class="flex flex-wrap items-center justify-between text-xs text-base-content/60 px-2 pt-2">
                    <span>{{ __('Tanggal Pengiriman') }}: {{ $submission->created_at?->translatedFormat('d F Y H:i') }}</span>
                    @if ($submission->assessed_at)
                        <span>{{ __('Terakhir Dinilai') }}: {{ $submission->assessed_at->translatedFormat('d F Y H:i') }}</span>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>
