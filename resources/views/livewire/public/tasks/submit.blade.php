<div class="mx-auto max-w-3xl space-y-8">
    {{-- Header --}}
    <div class="text-center space-y-2">
        <span class="badge badge-primary badge-outline font-semibold tracking-wider uppercase text-xs">JOTA-JOTI
            Activity</span>
        <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl text-base-content">
            {{ __('Form Pengiriman Tugas') }}
        </h1>
        <p class="text-base-content/70 mx-auto max-w-xl text-sm sm:text-base">
            {{ __('Kirimkan laporan tugas JOTA Digital dan JOTI Anda untuk dinilai oleh panitia. Pastikan data ID Peserta sudah terverifikasi.') }}
        </p>
    </div>

    {{-- Main Form Card --}}
    <div class="card bg-base-100 shadow-lift rounded-3xl border border-base-200/80 overflow-hidden">
        <form wire:submit="submit" class="card-body p-6 sm:p-10 space-y-6">
            {{-- Section 1: Identitas Peserta --}}
            <div class="space-y-4">
                <div class="flex items-center gap-2 pb-2 border-b border-base-200">
                    <span
                        class="flex size-7 items-center justify-center rounded-full bg-primary/10 text-primary font-bold text-xs">1</span>
                    <h2 class="font-bold text-base text-base-content">{{ __('Identitas Peserta') }}</h2>
                </div>

                <div class="space-y-2">
                    <label class="label pb-1" for="participant_code">
                        <span class="label-text font-semibold text-sm">{{ __('ID Peserta') }} <span
                                class="text-error">*</span></span>
                        <span
                            class="label-text-alt text-base-content/60">{{ __('Tertera pada kartu peserta, contoh: P-ABC12345') }}</span>
                    </label>
                    <div class="join w-full">
                        <input type="text" id="participant_code" wire:model.live.debounce.500ms="participant_code"
                            placeholder="Contoh: P-A1B2C3D4"
                            class="input input-bordered join-item w-full uppercase font-mono tracking-wider focus:outline-primary @error('participant_code') input-error @enderror" />
                        <button type="button" wire:click="lookupParticipant" class="btn btn-primary join-item">
                            <span wire:loading.remove wire:target="lookupParticipant, participant_code">
                                <x-ui.icon name="search" class="size-4" />
                            </span>
                            <span wire:loading wire:target="lookupParticipant, participant_code"
                                class="loading loading-spinner loading-xs"></span>
                            {{ __('Cek ID') }}
                        </button>
                    </div>
                    @error('participant_code')
                        <p class="text-error text-xs font-medium mt-1 flex items-center gap-1">
                            <x-ui.icon name="exclamation" class="size-3.5 shrink-0" /> {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Preview Identitas Peserta Jika Ditemukan --}}
                @if ($participant)
                    <div
                        class="bg-base-200/60 rounded-2xl p-4 sm:p-5 border border-primary/20 space-y-3 animate-fadeIn">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div class="bg-primary text-primary-content rounded-xl size-12 font-bold text-lg">
                                        {{ \Illuminate\Support\Str::of($participant->full_name)->substr(0, 1)->upper() }}
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-base text-base-content">{{ $participant->full_name }}
                                    </h3>
                                    <p class="text-xs text-base-content/70">
                                        {{ $participant->troop?->troop_name }}
                                        ({{ $participant->troop?->troop_number }})
                                    </p>
                                    <p class="text-xs font-medium text-primary mt-0.5">
                                        {{ $participant->troop?->unit?->label() }} · Nomor Grup:
                                        {{ $participant->group_number ?? '-' }}
                                    </p>
                                </div>
                            </div>
                            <span class="badge badge-success badge-sm font-semibold gap-1">
                                <x-ui.icon name="check" class="size-3" /> Terverifikasi
                            </span>
                        </div>

                        @if ($existingSubmission)
                            <div class="alert alert-info/10 border border-info/30 rounded-xl text-xs py-2.5 px-3">
                                <x-ui.icon name="info" class="size-4 text-info shrink-0" />
                                <div>
                                    <span class="font-bold">Status Tugas Saat Ini:</span>
                                    {{ $existingSubmission->final_status->label() }}
                                    @if ($existingSubmission->final_status === \App\Enums\TaskStatus::Revision)
                                        <p class="text-warning font-semibold mt-0.5">Catatan Revisi:
                                            {{ $existingSubmission->admin_notes ?: ($existingSubmission->jota_admin_note ?: $existingSubmission->joti_admin_note) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Section 2: Informasi JID & JJID --}}
            <div class="space-y-4 pt-2">
                <div class="flex items-center gap-2 pb-2 border-b border-base-200">
                    <span
                        class="flex size-7 items-center justify-center rounded-full bg-primary/10 text-primary font-bold text-xs">2</span>
                    <h2 class="font-bold text-base text-base-content">{{ __('Identitas Panggilan (JID & JJID)') }}</h2>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1">
                        <label class="label pb-1" for="jid">
                            <span class="label-text font-semibold text-sm">{{ __('JID') }} <span
                                    class="text-error">*</span></span>
                        </label>
                        <input type="text" id="jid" wire:model="jid" placeholder="Contoh: YB6ZBB"
                            class="input input-bordered w-full focus:outline-primary @error('jid') input-error @enderror" />
                        @error('jid')
                            <p class="text-error text-xs font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="label pb-1" for="jjid">
                            <span class="label-text font-semibold text-sm">{{ __('JJID') }} <span
                                    class="text-error">*</span></span>
                        </label>
                        <input type="text" id="jjid" wire:model="jjid" placeholder="Contoh: 123456"
                            class="input input-bordered w-full focus:outline-primary @error('jjid') input-error @enderror" />
                        @error('jjid')
                            <p class="text-error text-xs font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Section 3: Unggah Berkas Tugas PDF --}}
            <div class="space-y-4 pt-2">
                <div class="flex items-center gap-2 pb-2 border-b border-base-200">
                    <span
                        class="flex size-7 items-center justify-center rounded-full bg-primary/10 text-primary font-bold text-xs">3</span>
                    <h2 class="font-bold text-base text-base-content">
                        {{ __('Berkas Laporan Tugas (Format PDF, Maks. 10 MB)') }}</h2>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    {{-- Tugas JOTA Digital --}}
                    <div class="space-y-2">
                        <label class="label pb-1" for="jota_file">
                            <span class="label-text font-semibold text-sm">{{ __('Tugas JOTA Digital') }} <span
                                    class="text-error">*</span></span>
                        </label>
                        <div
                            class="border-2 border-dashed rounded-2xl p-4 text-center transition hover:border-primary/50 @error('jota_file') border-error bg-error/5 @else border-base-300 bg-base-200/30 @enderror">
                            <x-ui.icon name="document" class="size-8 mx-auto text-primary/70 mb-2" />
                            <input type="file" id="jota_file" wire:model="jota_file" accept=".pdf"
                                class="file-input file-input-bordered file-input-primary file-input-sm w-full max-w-xs" />
                            <p class="text-[11px] text-base-content/60 mt-1.5">
                                {{ __('Format PDF, ukuran maksimal 10 MB') }}</p>

                            <div wire:loading wire:target="jota_file"
                                class="mt-2 text-xs text-primary flex items-center justify-center gap-1.5">
                                <span class="loading loading-spinner loading-xs"></span>
                                {{ __('Mengunggah berkas...') }}
                            </div>

                            @if ($existingSubmission && $existingSubmission->jota_file_path && !$jota_file)
                                <div class="mt-2 badge badge-ghost badge-sm text-[11px] gap-1 font-mono">
                                    <x-ui.icon name="check" class="size-3 text-success" />
                                    {{ __('Berkas tersimpan sebelumnya') }}
                                </div>
                            @endif
                        </div>
                        @error('jota_file')
                            <p class="text-error text-xs font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tugas JOTI --}}
                    <div class="space-y-2">
                        <label class="label pb-1" for="joti_file">
                            <span class="label-text font-semibold text-sm">{{ __('Tugas JOTI') }} <span
                                    class="text-error">*</span></span>
                        </label>
                        <div
                            class="border-2 border-dashed rounded-2xl p-4 text-center transition hover:border-primary/50 @error('joti_file') border-error bg-error/5 @else border-base-300 bg-base-200/30 @enderror">
                            <x-ui.icon name="document" class="size-8 mx-auto text-primary/70 mb-2" />
                            <input type="file" id="joti_file" wire:model="joti_file" accept=".pdf"
                                class="file-input file-input-bordered file-input-primary file-input-sm w-full max-w-xs" />
                            <p class="text-[11px] text-base-content/60 mt-1.5">
                                {{ __('Format PDF, ukuran maksimal 10 MB') }}</p>

                            <div wire:loading wire:target="joti_file"
                                class="mt-2 text-xs text-primary flex items-center justify-center gap-1.5">
                                <span class="loading loading-spinner loading-xs"></span>
                                {{ __('Mengunggah berkas...') }}
                            </div>

                            @if ($existingSubmission && $existingSubmission->joti_file_path && !$joti_file)
                                <div class="mt-2 badge badge-ghost badge-sm text-[11px] gap-1 font-mono">
                                    <x-ui.icon name="check" class="size-3 text-success" />
                                    {{ __('Berkas tersimpan sebelumnya') }}
                                </div>
                            @endif
                        </div>
                        @error('joti_file')
                            <p class="text-error text-xs font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Section 4: Catatan Opsional --}}
            <div class="space-y-2 pt-2">
                <label class="label pb-1" for="notes">
                    <span class="label-text font-semibold text-sm">{{ __('Catatan / Pesan Tambahan') }} <span
                            class="text-base-content/50 font-normal">({{ __('Opsional') }})</span></span>
                </label>
                <textarea id="notes" wire:model="notes" rows="3"
                    placeholder="Tuliskan catatan tambahan terkait pengiriman tugas jika ada..."
                    class="textarea textarea-bordered w-full focus:outline-primary text-sm"></textarea>
                @error('notes')
                    <p class="text-error text-xs font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit Button --}}
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('tasks.status', ['code' => $participant_code]) }}"
                    class="btn btn-ghost text-sm order-2 sm:order-1">
                    <x-ui.icon name="search" class="size-4" /> {{ __('Cek Penilaian Tugas') }}
                </a>

                <button type="submit"
                    class="btn btn-primary btn-lg w-full sm:w-auto font-bold px-8 shadow-md order-1 sm:order-2"
                    wire:loading.attr="disabled">
                    <span wire:loading wire:target="submit" class="loading loading-spinner loading-sm"></span>
                    <x-ui.icon name="upload" class="size-5" />
                    {{ $existingSubmission ? __('Kirim Pembaruan Tugas') : __('Kirim Tugas Sekarang') }}
                </button>
            </div>
        </form>
    </div>
</div>
