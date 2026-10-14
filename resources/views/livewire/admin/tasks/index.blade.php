<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-base-content">{{ __('Penilaian Tugas JOTA-JOTI') }}</h1>
            <p class="text-sm text-base-content/60">{{ __('Evaluasi laporan kegiatan Tugas JOTA Digital dan Tugas JOTI yang dikirimkan peserta.') }}</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
        <div class="bg-base-100 rounded-2xl p-4 shadow-soft border border-base-200">
            <span class="text-xs font-semibold text-base-content/60">{{ __('Total Dikirim') }}</span>
            <p class="text-2xl font-extrabold text-base-content mt-1">{{ number_format($counts['total']) }}</p>
        </div>
        <div class="bg-base-100 rounded-2xl p-4 shadow-soft border border-info/20">
            <span class="text-xs font-semibold text-info flex items-center gap-1">
                <span class="size-2 rounded-full bg-info inline-block"></span> {{ __('Menunggu') }}
            </span>
            <p class="text-2xl font-extrabold text-info mt-1">{{ number_format($counts['pending']) }}</p>
        </div>
        <div class="bg-base-100 rounded-2xl p-4 shadow-soft border border-success/20">
            <span class="text-xs font-semibold text-success flex items-center gap-1">
                <span class="size-2 rounded-full bg-success inline-block"></span> {{ __('Selesai') }}
            </span>
            <p class="text-2xl font-extrabold text-success mt-1">{{ number_format($counts['completed']) }}</p>
        </div>
        <div class="bg-base-100 rounded-2xl p-4 shadow-soft border border-warning/20">
            <span class="text-xs font-semibold text-warning flex items-center gap-1">
                <span class="size-2 rounded-full bg-warning inline-block"></span> {{ __('Revisi') }}
            </span>
            <p class="text-2xl font-extrabold text-warning mt-1">{{ number_format($counts['revision']) }}</p>
        </div>
        <div class="bg-base-100 rounded-2xl p-4 shadow-soft border border-error/20">
            <span class="text-xs font-semibold text-error flex items-center gap-1">
                <span class="size-2 rounded-full bg-error inline-block"></span> {{ __('Ditolak') }}
            </span>
            <p class="text-2xl font-extrabold text-error mt-1">{{ number_format($counts['rejected']) }}</p>
        </div>
    </div>

    {{-- Filters & Search Toolbar --}}
    <div class="card bg-base-100 shadow-soft rounded-2xl p-4 border border-base-200 flex flex-col md:flex-row items-center gap-3">
        <div class="relative w-full md:flex-1">
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="Cari nama peserta, ID Peserta, gudep, JID, JJID..."
                class="input input-bordered input-sm w-full pl-9 focus:outline-primary"
            />
            <x-ui.icon name="search" class="size-4 text-base-content/40 absolute left-3 top-2.5" />
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto">
            <select wire:model.live="statusFilter" class="select select-bordered select-sm flex-1 md:w-44">
                <option value="all">{{ __('Semua Status Akhir') }}</option>
                <option value="pending">{{ __('Menunggu Penilaian') }}</option>
                <option value="completed">{{ __('Selesai') }}</option>
                <option value="revision">{{ __('Revisi') }}</option>
                <option value="rejected">{{ __('Ditolak') }}</option>
            </select>

            <select wire:model.live="unitFilter" class="select select-bordered select-sm flex-1 md:w-40">
                <option value="all">{{ __('Semua Golongan') }}</option>
                <option value="penggalang">{{ __('Penggalang') }}</option>
                <option value="penegak_pandega">{{ __('Penegak/Pandega') }}</option>
            </select>
        </div>
    </div>

    {{-- Submissions Table --}}
    <div class="card bg-base-100 shadow-soft rounded-2xl border border-base-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra table-sm">
                <thead class="bg-base-200/60 text-xs text-base-content/70">
                    <tr>
                        <th class="py-3 px-4">{{ __('Peserta') }}</th>
                        <th>{{ __('Gugusdepan') }}</th>
                        <th>{{ __('JID / JJID') }}</th>
                        <th>{{ __('Tugas JOTA') }}</th>
                        <th>{{ __('Tugas JOTI') }}</th>
                        <th>{{ __('Status Akhir') }}</th>
                        <th class="text-right px-4">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-200/60 text-xs">
                    @forelse ($submissions as $sub)
                        <tr class="hover:bg-base-200/40 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-sm text-base-content">{{ $sub->participant?->full_name }}</div>
                                <div class="font-mono text-[11px] text-primary font-semibold mt-0.5">
                                    {{ $sub->participant?->attendance_code }}
                                </div>
                            </td>
                            <td>
                                <div class="font-medium text-base-content/90">{{ $sub->participant?->troop?->troop_name }}</div>
                                <div class="text-[11px] text-base-content/60">
                                    {{ $sub->participant?->troop?->troop_number }} · {{ $sub->participant?->troop?->unit?->label() }}
                                </div>
                            </td>
                            <td>
                                <div class="font-mono text-[11px]"><span class="text-base-content/50">JID:</span> <span class="font-semibold">{{ $sub->jid }}</span></div>
                                <div class="font-mono text-[11px]"><span class="text-base-content/50">JJID:</span> <span class="font-semibold">{{ $sub->jjid }}</span></div>
                            </td>
                            <td>
                                <div class="flex items-center gap-1.5">
                                    <span @class(['badge badge-sm font-semibold text-[10px]', $sub->jota_status->badgeClass()])>
                                        {{ $sub->jota_status->label() }}
                                    </span>
                                    <a
                                        href="{{ route('tasks.download', ['submission' => $sub->id, 'type' => 'jota', 'inline' => 1]) }}"
                                        target="_blank"
                                        class="btn btn-ghost btn-xs btn-square text-primary"
                                        title="{{ __('Lihat Berkas JOTA') }}"
                                    >
                                        <x-ui.icon name="document" class="size-3.5" />
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-1.5">
                                    <span @class(['badge badge-sm font-semibold text-[10px]', $sub->joti_status->badgeClass()])>
                                        {{ $sub->joti_status->label() }}
                                    </span>
                                    <a
                                        href="{{ route('tasks.download', ['submission' => $sub->id, 'type' => 'joti', 'inline' => 1]) }}"
                                        target="_blank"
                                        class="btn btn-ghost btn-xs btn-square text-accent"
                                        title="{{ __('Lihat Berkas JOTI') }}"
                                    >
                                        <x-ui.icon name="document" class="size-3.5" />
                                    </a>
                                </div>
                            </td>
                            <td>
                                <span @class(['badge badge-sm font-bold text-[11px] gap-1', $sub->final_status->badgeClass()])>
                                    {{ $sub->final_status->label() }}
                                </span>
                            </td>
                            <td class="text-right px-4">
                                <button
                                    type="button"
                                    wire:click="openAssessModal('{{ $sub->id }}')"
                                    class="btn btn-primary btn-xs gap-1 font-semibold"
                                >
                                    <x-ui.icon name="pencil" class="size-3.5" />
                                    {{ __('Nilai') }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-base-content/50">
                                <x-ui.icon name="document" class="size-10 mx-auto text-base-content/30 mb-2" />
                                <p class="font-medium text-sm">{{ __('Tidak ada data pengiriman tugas yang ditemukan.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($submissions->hasPages())
            <div class="p-4 border-t border-base-200">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Penilaian Tugas --}}
    @if ($showAssessModal && $selectedSubmission)
        <div class="modal modal-open">
            <div class="modal-box max-w-3xl rounded-3xl p-6 sm:p-8 space-y-6">
                {{-- Modal Header --}}
                <div class="flex items-start justify-between pb-3 border-b border-base-200">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-extrabold text-lg text-base-content">{{ __('Penilaian Tugas Peserta') }}</h3>
                            <span class="badge badge-primary badge-sm font-mono font-bold">{{ $selectedSubmission->participant?->attendance_code }}</span>
                        </div>
                        <p class="text-xs text-base-content/70 mt-0.5">
                            {{ $selectedSubmission->participant?->full_name }} · {{ $selectedSubmission->participant?->troop?->troop_name }} ({{ $selectedSubmission->participant?->troop?->unit?->label() }})
                        </p>
                    </div>
                    <button type="button" wire:click="closeAssessModal" class="btn btn-ghost btn-sm btn-square">
                        <x-ui.icon name="x-circle" class="size-5" />
                    </button>
                </div>

                {{-- Info Berkas & ID Pengirim --}}
                <div class="bg-base-200/50 rounded-2xl p-4 grid gap-3 sm:grid-cols-2 text-xs">
                    <div>
                        <span class="text-base-content/60 font-semibold">{{ __('Panggilan:') }}</span>
                        <p class="font-mono mt-0.5 font-bold">JID: {{ $selectedSubmission->jid }} · JJID: {{ $selectedSubmission->jjid }}</p>
                    </div>
                    <div>
                        <span class="text-base-content/60 font-semibold">{{ __('Catatan dari Peserta:') }}</span>
                        <p class="mt-0.5 text-base-content/80 italic">{{ $selectedSubmission->notes ?: __('(Tidak ada catatan)') }}</p>
                    </div>

                    <div class="sm:col-span-2 flex flex-wrap gap-2 pt-2 border-t border-base-300/50">
                        <a
                            href="{{ route('tasks.download', ['submission' => $selectedSubmission->id, 'type' => 'jota', 'inline' => 1]) }}"
                            target="_blank"
                            class="btn btn-outline btn-primary btn-xs gap-1.5"
                        >
                            <x-ui.icon name="document" class="size-3.5" /> {{ __('Buka PDF Tugas JOTA Digital') }}
                        </a>
                        <a
                            href="{{ route('tasks.download', ['submission' => $selectedSubmission->id, 'type' => 'joti', 'inline' => 1]) }}"
                            target="_blank"
                            class="btn btn-outline btn-accent btn-xs gap-1.5"
                        >
                            <x-ui.icon name="document" class="size-3.5" /> {{ __('Buka PDF Tugas JOTI') }}
                        </a>
                    </div>
                </div>

                {{-- Form Penilaian --}}
                <form wire:submit="saveAssessment" class="space-y-5">
                    <div class="grid gap-5 md:grid-cols-2">
                        {{-- Evaluasi JOTA Digital --}}
                        <div class="card bg-base-100 border border-base-300 rounded-2xl p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-sm text-base-content flex items-center gap-1.5">
                                    <span class="size-2 rounded-full bg-primary inline-block"></span> {{ __('Tugas JOTA Digital') }}
                                </h4>
                            </div>

                            <div class="space-y-1">
                                <label class="label-text text-xs font-semibold text-base-content/70">{{ __('Status Tugas JOTA:') }}</label>
                                <select wire:model.live="jota_status" class="select select-bordered select-sm w-full font-semibold">
                                    <option value="pending">{{ __('Menunggu Penilaian') }}</option>
                                    <option value="completed">{{ __('Selesai') }}</option>
                                    <option value="revision">{{ __('Revisi') }}</option>
                                    <option value="rejected">{{ __('Ditolak') }}</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="label-text text-xs font-semibold text-base-content/70">{{ __('Catatan Reviewer JOTA:') }}</label>
                                <textarea
                                    wire:model="jota_admin_note"
                                    rows="2"
                                    placeholder="Catatan koreksi/masukan untuk tugas JOTA..."
                                    class="textarea textarea-bordered textarea-sm w-full text-xs"
                                ></textarea>
                            </div>
                        </div>

                        {{-- Evaluasi JOTI --}}
                        <div class="card bg-base-100 border border-base-300 rounded-2xl p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-sm text-base-content flex items-center gap-1.5">
                                    <span class="size-2 rounded-full bg-accent inline-block"></span> {{ __('Tugas JOTI') }}
                                </h4>
                            </div>

                            <div class="space-y-1">
                                <label class="label-text text-xs font-semibold text-base-content/70">{{ __('Status Tugas JOTI:') }}</label>
                                <select wire:model.live="joti_status" class="select select-bordered select-sm w-full font-semibold">
                                    <option value="pending">{{ __('Menunggu Penilaian') }}</option>
                                    <option value="completed">{{ __('Selesai') }}</option>
                                    <option value="revision">{{ __('Revisi') }}</option>
                                    <option value="rejected">{{ __('Ditolak') }}</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="label-text text-xs font-semibold text-base-content/70">{{ __('Catatan Reviewer JOTI:') }}</label>
                                <textarea
                                    wire:model="joti_admin_note"
                                    rows="2"
                                    placeholder="Catatan koreksi/masukan untuk tugas JOTI..."
                                    class="textarea textarea-bordered textarea-sm w-full text-xs"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Status Akhir Keseluruhan --}}
                    <div class="bg-base-200/40 rounded-2xl p-4 border border-base-300/80 space-y-3">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-1">
                                <label class="label-text text-xs font-bold text-base-content">{{ __('Status Akhir Keseluruhan:') }} <span class="text-error">*</span></label>
                                <select wire:model="final_status" class="select select-bordered select-sm w-full font-bold">
                                    <option value="pending">{{ __('Menunggu Penilaian') }}</option>
                                    <option value="completed">{{ __('Selesai') }}</option>
                                    <option value="revision">{{ __('Revisi') }}</option>
                                    <option value="rejected">{{ __('Ditolak') }}</option>
                                </select>
                                <p class="text-[11px] text-base-content/60">{{ __('Otomatis dihitung dari status kedua tugas atau dapat diubah manual.') }}</p>
                            </div>

                            <div class="space-y-1">
                                <label class="label-text text-xs font-semibold text-base-content/70">{{ __('Catatan Evaluasi Umum (Opsional):') }}</label>
                                <textarea
                                    wire:model="admin_notes"
                                    rows="2"
                                    placeholder="Catatan umum yang akan tampil di halaman cek status peserta..."
                                    class="textarea textarea-bordered textarea-sm w-full text-xs"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeAssessModal" class="btn btn-ghost btn-sm">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm px-6 font-bold" wire:loading.attr="disabled">
                            <span wire:loading wire:target="saveAssessment" class="loading loading-spinner loading-xs"></span>
                            {{ __('Simpan Penilaian') }}
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop bg-black/40" wire:click="closeAssessModal"></div>
        </div>
    @endif
</div>
