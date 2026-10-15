<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight sm:text-3xl flex items-center gap-2.5">
                <x-ui.icon name="award" class="size-8 text-primary" />
                <span>Pengaturan Template Sertifikat</span>
            </h1>
            <p class="text-base-content/70 mt-1 text-sm">
                Unggah desain sertifikat gambar (A4 landscape) dan atur posisi peletakan teks ID peserta, nama peserta, serta nomor dan nama gugusdepan secara visual dan presisi.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.certificates.preview', $activeTab) }}" target="_blank" class="btn btn-outline btn-sm gap-2">
                <x-ui.icon name="printer" class="size-4" />
                Lihat Contoh PDF
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="tabs tabs-boxed bg-base-200/80 p-1.5 rounded-2xl inline-flex w-full sm:w-auto shadow-inner">
        <button type="button" 
            wire:click="$set('activeTab', 'participant')"
            @class([
                'tab flex-1 sm:flex-initial gap-2 font-semibold transition-all duration-200 py-2.5 px-6 rounded-xl',
                'tab-active !bg-primary !text-primary-content shadow-sm' => $activeTab === 'participant',
            ])>
            <x-ui.icon name="user" class="size-4" />
            Sertifikat Peserta
        </button>

        <button type="button" 
            wire:click="$set('activeTab', 'troop')"
            @class([
                'tab flex-1 sm:flex-initial gap-2 font-semibold transition-all duration-200 py-2.5 px-6 rounded-xl',
                'tab-active !bg-primary !text-primary-content shadow-sm' => $activeTab === 'troop',
            ])>
            <x-ui.icon name="flag" class="size-4" />
            Sertifikat Gugusdepan
        </button>
    </div>

    {{-- TAB 1: SERTIFIKAT PESERTA --}}
    @if ($activeTab === 'participant')
        <form wire:submit="saveParticipant" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                {{-- Kiri: Interactive Visual Canvas (Aspect Ratio 297:210) --}}
                <div class="lg:col-span-7 xl:col-span-8 space-y-3">
                    <div class="card bg-base-100 shadow-soft border border-base-200 overflow-hidden">
                        <div class="card-body p-4 sm:p-5">
                            <div class="flex items-center justify-between pb-3 border-b border-base-200">
                                <div class="flex items-center gap-2">
                                    <span class="badge badge-primary badge-sm font-semibold">Live Preview</span>
                                    <span class="text-xs text-base-content/60">A4 Landscape (297 x 210 mm)</span>
                                </div>
                                <div class="text-xs text-base-content/60 flex items-center gap-2">
                                    <span class="inline-block size-2 rounded-full bg-success"></span>
                                    <span>Teks dapat digeser langsung di kanvas</span>
                                </div>
                            </div>

                            {{-- Canvas Wrapper dengan Alpine.js Drag Logic --}}
                            <div 
                                x-data="{
                                    nameX: @entangle('p_name_x'),
                                    nameY: @entangle('p_name_y'),
                                    nameAlign: @entangle('p_name_align'),
                                    nameSize: @entangle('p_name_size'),
                                    nameColor: @entangle('p_name_color'),
                                    nameBold: @entangle('p_name_bold'),

                                    idX: @entangle('p_id_x'),
                                    idY: @entangle('p_id_y'),
                                    idAlign: @entangle('p_id_align'),
                                    idSize: @entangle('p_id_size'),
                                    idColor: @entangle('p_id_color'),
                                    idBold: @entangle('p_id_bold'),

                                    dragging: null,

                                    startDrag(target, event) {
                                        this.dragging = target;
                                        event.preventDefault();
                                    },
                                    onMouseMove(event) {
                                        if (!this.dragging) return;
                                        const rect = this.$refs.canvas.getBoundingClientRect();
                                        let clientX = event.clientX || (event.touches && event.touches[0].clientX);
                                        let clientY = event.clientY || (event.touches && event.touches[0].clientY);
                                        if (!clientX || !clientY) return;

                                        let x = ((clientX - rect.left) / rect.width) * 100;
                                        let y = ((clientY - rect.top) / rect.height) * 100;

                                        x = Math.max(0, Math.min(100, Math.round(x * 10) / 10));
                                        y = Math.max(0, Math.min(100, Math.round(y * 10) / 10));

                                        if (this.dragging === 'name') {
                                            this.nameX = x;
                                            this.nameY = y;
                                        } else if (this.dragging === 'id') {
                                            this.idX = x;
                                            this.idY = y;
                                        }
                                    },
                                    stopDrag() {
                                        this.dragging = null;
                                    }
                                }"
                                @mousemove.window="onMouseMove($event)"
                                @mouseup.window="stopDrag()"
                                @touchmove.window="onMouseMove($event)"
                                @touchend.window="stopDrag()"
                                class="mt-4 relative select-none"
                            >
                                <div 
                                    x-ref="canvas"
                                    class="relative w-full rounded-xl overflow-hidden border-2 border-base-300 shadow-md bg-neutral-900"
                                    style="aspect-ratio: 297 / 210;"
                                >
                                    {{-- Background: Image Template atau Default Design --}}
                                    @if ($participant_template)
                                        <img src="{{ $participant_template->temporaryUrl() }}" alt="Template Peserta Baru" class="absolute inset-0 w-full h-full object-fill pointer-events-none" />
                                    @elseif ($participant_template_url)
                                        <img src="{{ $participant_template_url }}" alt="Template Peserta" class="absolute inset-0 w-full h-full object-fill pointer-events-none" />
                                    @else
                                        {{-- Placeholder Desain Bawaan --}}
                                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-950 via-purple-900 to-indigo-900 p-6 flex flex-col justify-between text-white pointer-events-none">
                                            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                                                <div class="flex items-center gap-2">
                                                    <div class="size-8 rounded-full bg-warning/20 border border-warning/40 flex items-center justify-center text-warning font-bold text-xs">JOTA</div>
                                                    <span class="text-xs font-semibold uppercase tracking-wider text-white/80">Kwartir Cabang Gerakan Pramuka</span>
                                                </div>
                                                <span class="text-xs font-mono text-warning/80">DESAIN BAWAAN</span>
                                            </div>
                                            <div class="text-center space-y-1 my-auto">
                                                <h3 class="text-2xl font-serif font-black tracking-widest text-warning">SERTIFIKAT KEIKUTSERTAAN</h3>
                                                <p class="text-xs text-white/70 italic">Diberikan sebagai penghargaan atas partisipasi aktif peserta</p>
                                            </div>
                                            <div class="flex justify-between items-end border-t border-white/10 pt-3 text-[10px] text-white/50">
                                                <span>A4 Landscape • 297 x 210 mm</span>
                                                <span>Unggah template untuk mengganti latar belakang ini</span>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Grid overlay halus untuk panduan presisi --}}
                                    <div class="absolute inset-0 pointer-events-none opacity-20 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>

                                    {{-- ELEMEN 1: NAMA PESERTA --}}
                                    <div 
                                        @mousedown="startDrag('name', $event)"
                                        @touchstart="startDrag('name', $event)"
                                        class="absolute cursor-move transition-transform active:scale-105 group"
                                        :style="`
                                            left: ${nameX}%; 
                                            top: ${nameY}%; 
                                            transform: translate(${nameAlign === 'center' ? '-50%' : (nameAlign === 'right' ? '-100%' : '0')}, -50%);
                                        `"
                                    >
                                        <div 
                                            class="px-3 py-1 rounded border border-dashed border-primary/80 bg-primary/10 backdrop-blur-xs group-hover:border-primary group-hover:bg-primary/20 shadow-sm transition"
                                        >
                                            <span 
                                                class="whitespace-nowrap transition-all"
                                                :style="`
                                                    font-size: clamp(10px, ${nameSize * 0.42}px, 34px);
                                                    color: ${nameColor};
                                                    font-weight: ${nameBold ? '700' : '400'};
                                                    text-align: ${nameAlign};
                                                `"
                                            >
                                                Ahmad Dahlan, S.Kom.
                                            </span>
                                        </div>
                                        <div class="absolute -top-5 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition badge badge-xs badge-primary font-mono whitespace-nowrap pointer-events-none">
                                            Nama Peserta (<span x-text="nameX"></span>%, <span x-text="nameY"></span>%)
                                        </div>
                                    </div>

                                    {{-- ELEMEN 2: ID PESERTA --}}
                                    <div 
                                        @mousedown="startDrag('id', $event)"
                                        @touchstart="startDrag('id', $event)"
                                        class="absolute cursor-move transition-transform active:scale-105 group"
                                        :style="`
                                            left: ${idX}%; 
                                            top: ${idY}%; 
                                            transform: translate(${idAlign === 'center' ? '-50%' : (idAlign === 'right' ? '-100%' : '0')}, -50%);
                                        `"
                                    >
                                        <div 
                                            class="px-2.5 py-0.5 rounded border border-dashed border-secondary/80 bg-secondary/10 backdrop-blur-xs group-hover:border-secondary group-hover:bg-secondary/20 shadow-sm transition"
                                        >
                                            <span 
                                                class="whitespace-nowrap font-mono transition-all"
                                                :style="`
                                                    font-size: clamp(8px, ${idSize * 0.42}px, 24px);
                                                    color: ${idColor};
                                                    font-weight: ${idBold ? '700' : '400'};
                                                    text-align: ${idAlign};
                                                `"
                                            >
                                                ID: JJID36064
                                            </span>
                                        </div>
                                        <div class="absolute -bottom-5 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition badge badge-xs badge-secondary font-mono whitespace-nowrap pointer-events-none">
                                            ID Peserta (<span x-text="idX"></span>%, <span x-text="idY"></span>%)
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-base-content/60 mt-3 flex items-center justify-between">
                                <span>Tips: Klik dan tahan teks di atas untuk menggeser posisinya secara bebas.</span>
                                <span class="font-mono">Rasio standar A4: 1.414</span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Kanan: Panel Kontrol Pengaturan & Unggah --}}
                <div class="lg:col-span-5 xl:col-span-4 space-y-5">
                    {{-- Card Unggah Template Gambar --}}
                    <div class="card bg-base-100 shadow-soft border border-base-200">
                        <div class="card-body p-4 sm:p-5 space-y-4">
                            <h2 class="card-title text-base font-bold flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <x-ui.icon name="upload" class="size-5 text-primary" />
                                    Berkas Template Gambar
                                </span>
                                @if ($participant_template_url || $participant_template)
                                    <span class="badge badge-success badge-sm font-semibold">Kustom Aktif</span>
                                @else
                                    <span class="badge badge-ghost badge-sm">Bawaan</span>
                                @endif
                            </h2>

                            <div class="space-y-3">
                                <div>
                                    <label class="label pt-0 pb-1.5">
                                        <span class="label-text text-xs font-semibold">Unggah Template (JPG/PNG/WebP, Max 5MB)</span>
                                    </label>
                                    <input 
                                        type="file" 
                                        wire:model="participant_template" 
                                        accept="image/png,image/jpeg,image/webp" 
                                        class="file-input file-input-bordered file-input-sm w-full"
                                    />
                                    @error('participant_template')
                                        <span class="text-xs text-error mt-1 block">{{ $message }}</span>
                                    @enderror
                                    <div wire:loading wire:target="participant_template" class="text-xs text-primary mt-1 flex items-center gap-1.5">
                                        <span class="loading loading-spinner loading-xs"></span> Mengunggah pratinjau...
                                    </div>
                                </div>

                                <p class="text-[11px] text-base-content/60 leading-relaxed">
                                    Gunakan resolusi tinggi seperti <strong>3508 x 2480 px</strong> (300 DPI) atau <strong>1920 x 1357 px</strong> agar hasil cetak PDF tajam dan tidak pecah.
                                </p>

                                @if ($participant_template_url)
                                    <div class="pt-2 border-t border-base-200 flex items-center justify-between">
                                        <span class="text-xs text-base-content/70">Template tersimpan aktif</span>
                                        <button 
                                            type="button" 
                                            wire:click="deleteParticipantTemplate" 
                                            wire:confirm="Yakin ingin menghapus template gambar kustom dan kembali ke desain bawaan?"
                                            class="btn btn-error btn-xs btn-outline gap-1"
                                        >
                                            <x-ui.icon name="trash" class="size-3.5" />
                                            Hapus Template
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Card Posisi Nama Peserta --}}
                    <div class="card bg-base-100 shadow-soft border border-base-200">
                        <div class="card-body p-4 sm:p-5 space-y-3">
                            <h3 class="font-bold text-sm text-primary flex items-center gap-1.5">
                                <span class="size-2 rounded-full bg-primary inline-block"></span>
                                Posisi & Tipografi Nama Peserta
                            </h3>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Posisi X (%)</span></label>
                                    <input type="number" step="0.1" min="0" max="100" wire:model.live="p_name_x" class="input input-sm input-bordered w-full font-mono text-xs" />
                                </div>
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Posisi Y (%)</span></label>
                                    <input type="number" step="0.1" min="0" max="100" wire:model.live="p_name_y" class="input input-sm input-bordered w-full font-mono text-xs" />
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Ukuran Font</span></label>
                                    <input type="number" min="8" max="72" wire:model.live="p_name_size" class="input input-sm input-bordered w-full text-xs" />
                                </div>
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Perataan</span></label>
                                    <select wire:model.live="p_name_align" class="select select-sm select-bordered w-full text-xs">
                                        <option value="center">Tengah</option>
                                        <option value="left">Kiri</option>
                                        <option value="right">Kanan</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Warna</span></label>
                                    <div class="flex items-center gap-1">
                                        <input type="color" wire:model.live="p_name_color" class="size-8 rounded cursor-pointer border border-base-300 p-0.5" />
                                        <input type="text" wire:model.live="p_name_color" class="input input-sm input-bordered w-full font-mono text-[11px] px-1" />
                                    </div>
                                </div>
                            </div>

                            <label class="label cursor-pointer justify-start gap-2 pt-1 pb-0">
                                <input type="checkbox" wire:model.live="p_name_bold" class="checkbox checkbox-primary checkbox-xs rounded" />
                                <span class="label-text text-xs font-semibold">Tebal (Bold)</span>
                            </label>
                        </div>
                    </div>

                    {{-- Card Posisi ID Peserta --}}
                    <div class="card bg-base-100 shadow-soft border border-base-200">
                        <div class="card-body p-4 sm:p-5 space-y-3">
                            <h3 class="font-bold text-sm text-secondary flex items-center gap-1.5">
                                <span class="size-2 rounded-full bg-secondary inline-block"></span>
                                Posisi & Tipografi ID Peserta
                            </h3>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Posisi X (%)</span></label>
                                    <input type="number" step="0.1" min="0" max="100" wire:model.live="p_id_x" class="input input-sm input-bordered w-full font-mono text-xs" />
                                </div>
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Posisi Y (%)</span></label>
                                    <input type="number" step="0.1" min="0" max="100" wire:model.live="p_id_y" class="input input-sm input-bordered w-full font-mono text-xs" />
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Ukuran Font</span></label>
                                    <input type="number" min="8" max="72" wire:model.live="p_id_size" class="input input-sm input-bordered w-full text-xs" />
                                </div>
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Perataan</span></label>
                                    <select wire:model.live="p_id_align" class="select select-sm select-bordered w-full text-xs">
                                        <option value="center">Tengah</option>
                                        <option value="left">Kiri</option>
                                        <option value="right">Kanan</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Warna</span></label>
                                    <div class="flex items-center gap-1">
                                        <input type="color" wire:model.live="p_id_color" class="size-8 rounded cursor-pointer border border-base-300 p-0.5" />
                                        <input type="text" wire:model.live="p_id_color" class="input input-sm input-bordered w-full font-mono text-[11px] px-1" />
                                    </div>
                                </div>
                            </div>

                            <label class="label cursor-pointer justify-start gap-2 pt-1 pb-0">
                                <input type="checkbox" wire:model.live="p_id_bold" class="checkbox checkbox-secondary checkbox-xs rounded" />
                                <span class="label-text text-xs font-semibold">Tebal (Bold)</span>
                            </label>
                        </div>
                    </div>

                    {{-- Tombol Aksi Simpan & Reset --}}
                    <div class="flex flex-col gap-2 pt-2">
                        <button type="submit" class="btn btn-primary w-full gap-2 font-bold shadow-md">
                            <x-ui.icon name="check" class="size-5" />
                            Simpan Perubahan Sertifikat Peserta
                        </button>

                        <div class="grid grid-cols-2 gap-2">
                            <button 
                                type="button" 
                                wire:click="resetParticipantLayout" 
                                class="btn btn-outline btn-sm gap-1.5"
                            >
                                <x-ui.icon name="arrow-path" class="size-4" />
                                Reset Posisi
                            </button>

                            <a 
                                href="{{ route('admin.certificates.preview', 'participant') }}" 
                                target="_blank" 
                                class="btn btn-ghost btn-sm gap-1.5"
                            >
                                <x-ui.icon name="eye" class="size-4" />
                                Preview PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif

    {{-- TAB 2: SERTIFIKAT GUGUSDEPAN --}}
    @if ($activeTab === 'troop')
        <form wire:submit="saveTroop" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                {{-- Kiri: Interactive Visual Canvas (Aspect Ratio 297:210) --}}
                <div class="lg:col-span-7 xl:col-span-8 space-y-3">
                    <div class="card bg-base-100 shadow-soft border border-base-200 overflow-hidden">
                        <div class="card-body p-4 sm:p-5">
                            <div class="flex items-center justify-between pb-3 border-b border-base-200">
                                <div class="flex items-center gap-2">
                                    <span class="badge badge-primary badge-sm font-semibold">Live Preview</span>
                                    <span class="text-xs text-base-content/60">A4 Landscape (297 x 210 mm)</span>
                                </div>
                                <div class="text-xs text-base-content/60 flex items-center gap-2">
                                    <span class="inline-block size-2 rounded-full bg-success"></span>
                                    <span>Teks dapat digeser langsung di kanvas</span>
                                </div>
                            </div>

                            {{-- Canvas Wrapper dengan Alpine.js Drag Logic --}}
                            <div 
                                x-data="{
                                    nameX: @entangle('t_name_x'),
                                    nameY: @entangle('t_name_y'),
                                    nameAlign: @entangle('t_name_align'),
                                    nameSize: @entangle('t_name_size'),
                                    nameColor: @entangle('t_name_color'),
                                    nameBold: @entangle('t_name_bold'),

                                    numberX: @entangle('t_number_x'),
                                    numberY: @entangle('t_number_y'),
                                    numberAlign: @entangle('t_number_align'),
                                    numberSize: @entangle('t_number_size'),
                                    numberColor: @entangle('t_number_color'),
                                    numberBold: @entangle('t_number_bold'),

                                    dragging: null,

                                    startDrag(target, event) {
                                        this.dragging = target;
                                        event.preventDefault();
                                    },
                                    onMouseMove(event) {
                                        if (!this.dragging) return;
                                        const rect = this.$refs.troopCanvas.getBoundingClientRect();
                                        let clientX = event.clientX || (event.touches && event.touches[0].clientX);
                                        let clientY = event.clientY || (event.touches && event.touches[0].clientY);
                                        if (!clientX || !clientY) return;

                                        let x = ((clientX - rect.left) / rect.width) * 100;
                                        let y = ((clientY - rect.top) / rect.height) * 100;

                                        x = Math.max(0, Math.min(100, Math.round(x * 10) / 10));
                                        y = Math.max(0, Math.min(100, Math.round(y * 10) / 10));

                                        if (this.dragging === 'troop_name') {
                                            this.nameX = x;
                                            this.nameY = y;
                                        } else if (this.dragging === 'troop_number') {
                                            this.numberX = x;
                                            this.numberY = y;
                                        }
                                    },
                                    stopDrag() {
                                        this.dragging = null;
                                    }
                                }"
                                @mousemove.window="onMouseMove($event)"
                                @mouseup.window="stopDrag()"
                                @touchmove.window="onMouseMove($event)"
                                @touchend.window="stopDrag()"
                                class="mt-4 relative select-none"
                            >
                                <div 
                                    x-ref="troopCanvas"
                                    class="relative w-full rounded-xl overflow-hidden border-2 border-base-300 shadow-md bg-neutral-900"
                                    style="aspect-ratio: 297 / 210;"
                                >
                                    {{-- Background: Image Template atau Default Design --}}
                                    @if ($troop_template)
                                        <img src="{{ $troop_template->temporaryUrl() }}" alt="Template Gudep Baru" class="absolute inset-0 w-full h-full object-fill pointer-events-none" />
                                    @elseif ($troop_template_url)
                                        <img src="{{ $troop_template_url }}" alt="Template Gudep" class="absolute inset-0 w-full h-full object-fill pointer-events-none" />
                                    @else
                                        {{-- Placeholder Desain Bawaan --}}
                                        <div class="absolute inset-0 bg-gradient-to-br from-purple-950 via-indigo-900 to-purple-900 p-6 flex flex-col justify-between text-white pointer-events-none">
                                            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                                                <div class="flex items-center gap-2">
                                                    <div class="size-8 rounded-full bg-secondary/20 border border-secondary/40 flex items-center justify-center text-secondary font-bold text-xs">GUDEP</div>
                                                    <span class="text-xs font-semibold uppercase tracking-wider text-white/80">Kwartir Cabang Gerakan Pramuka</span>
                                                </div>
                                                <span class="text-xs font-mono text-secondary/80">DESAIN BAWAAN</span>
                                            </div>
                                            <div class="text-center space-y-1 my-auto">
                                                <h3 class="text-2xl font-serif font-black tracking-widest text-secondary">PIAGAM PENGHARGAAN GUDEP</h3>
                                                <p class="text-xs text-white/70 italic">Diberikan kepada Gugusdepan atas partisipasi aktif dalam kegiatan</p>
                                            </div>
                                            <div class="flex justify-between items-end border-t border-white/10 pt-3 text-[10px] text-white/50">
                                                <span>A4 Landscape • 297 x 210 mm</span>
                                                <span>Unggah template untuk mengganti latar belakang ini</span>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Grid overlay halus --}}
                                    <div class="absolute inset-0 pointer-events-none opacity-20 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px]"></div>

                                    {{-- ELEMEN 1: NAMA GUGUSDEPAN --}}
                                    <div 
                                        @mousedown="startDrag('troop_name', $event)"
                                        @touchstart="startDrag('troop_name', $event)"
                                        class="absolute cursor-move transition-transform active:scale-105 group"
                                        :style="`
                                            left: ${nameX}%; 
                                            top: ${nameY}%; 
                                            transform: translate(${nameAlign === 'center' ? '-50%' : (nameAlign === 'right' ? '-100%' : '0')}, -50%);
                                        `"
                                    >
                                        <div 
                                            class="px-3 py-1 rounded border border-dashed border-primary/80 bg-primary/10 backdrop-blur-xs group-hover:border-primary group-hover:bg-primary/20 shadow-sm transition"
                                        >
                                            <span 
                                                class="whitespace-nowrap transition-all"
                                                :style="`
                                                    font-size: clamp(10px, ${nameSize * 0.42}px, 34px);
                                                    color: ${nameColor};
                                                    font-weight: ${nameBold ? '700' : '400'};
                                                    text-align: ${nameAlign};
                                                `"
                                            >
                                                Pangkalan SMAN 1 Padangsidimpuan
                                            </span>
                                        </div>
                                        <div class="absolute -top-5 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition badge badge-xs badge-primary font-mono whitespace-nowrap pointer-events-none">
                                            Nama Gudep (<span x-text="nameX"></span>%, <span x-text="nameY"></span>%)
                                        </div>
                                    </div>

                                    {{-- ELEMEN 2: NOMOR GUGUSDEPAN --}}
                                    <div 
                                        @mousedown="startDrag('troop_number', $event)"
                                        @touchstart="startDrag('troop_number', $event)"
                                        class="absolute cursor-move transition-transform active:scale-105 group"
                                        :style="`
                                            left: ${numberX}%; 
                                            top: ${numberY}%; 
                                            transform: translate(${numberAlign === 'center' ? '-50%' : (numberAlign === 'right' ? '-100%' : '0')}, -50%);
                                        `"
                                    >
                                        <div 
                                            class="px-2.5 py-0.5 rounded border border-dashed border-secondary/80 bg-secondary/10 backdrop-blur-xs group-hover:border-secondary group-hover:bg-secondary/20 shadow-sm transition"
                                        >
                                            <span 
                                                class="whitespace-nowrap font-mono transition-all"
                                                :style="`
                                                    font-size: clamp(8px, ${numberSize * 0.42}px, 24px);
                                                    color: ${numberColor};
                                                    font-weight: ${numberBold ? '700' : '400'};
                                                    text-align: ${numberAlign};
                                                `"
                                            >
                                                Gudep 01.001 - 01.002
                                            </span>
                                        </div>
                                        <div class="absolute -bottom-5 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition badge badge-xs badge-secondary font-mono whitespace-nowrap pointer-events-none">
                                            Nomor Gudep (<span x-text="numberX"></span>%, <span x-text="numberY"></span>%)
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-base-content/60 mt-3 flex items-center justify-between">
                                <span>Tips: Klik dan tahan teks di atas untuk menggeser posisinya secara bebas.</span>
                                <span class="font-mono">Rasio standar A4: 1.414</span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Kanan: Panel Kontrol Pengaturan & Unggah --}}
                <div class="lg:col-span-5 xl:col-span-4 space-y-5">
                    {{-- Card Unggah Template Gambar --}}
                    <div class="card bg-base-100 shadow-soft border border-base-200">
                        <div class="card-body p-4 sm:p-5 space-y-4">
                            <h2 class="card-title text-base font-bold flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <x-ui.icon name="upload" class="size-5 text-primary" />
                                    Berkas Template Gambar
                                </span>
                                @if ($troop_template_url || $troop_template)
                                    <span class="badge badge-success badge-sm font-semibold">Kustom Aktif</span>
                                @else
                                    <span class="badge badge-ghost badge-sm">Bawaan</span>
                                @endif
                            </h2>

                            <div class="space-y-3">
                                <div>
                                    <label class="label pt-0 pb-1.5">
                                        <span class="label-text text-xs font-semibold">Unggah Template (JPG/PNG/WebP, Max 5MB)</span>
                                    </label>
                                    <input 
                                        type="file" 
                                        wire:model="troop_template" 
                                        accept="image/png,image/jpeg,image/webp" 
                                        class="file-input file-input-bordered file-input-sm w-full"
                                    />
                                    @error('troop_template')
                                        <span class="text-xs text-error mt-1 block">{{ $message }}</span>
                                    @enderror
                                    <div wire:loading wire:target="troop_template" class="text-xs text-primary mt-1 flex items-center gap-1.5">
                                        <span class="loading loading-spinner loading-xs"></span> Mengunggah pratinjau...
                                    </div>
                                </div>

                                <p class="text-[11px] text-base-content/60 leading-relaxed">
                                    Format landscape A4 (297 x 210 mm). Disarankan resolusi <strong>3508 x 2480 px</strong> untuk cetak tajam.
                                </p>

                                @if ($troop_template_url)
                                    <div class="pt-2 border-t border-base-200 flex items-center justify-between">
                                        <span class="text-xs text-base-content/70">Template tersimpan aktif</span>
                                        <button 
                                            type="button" 
                                            wire:click="deleteTroopTemplate" 
                                            wire:confirm="Yakin ingin menghapus template gambar kustom dan kembali ke desain bawaan?"
                                            class="btn btn-error btn-xs btn-outline gap-1"
                                        >
                                            <x-ui.icon name="trash" class="size-3.5" />
                                            Hapus Template
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Card Posisi Nama Gugusdepan --}}
                    <div class="card bg-base-100 shadow-soft border border-base-200">
                        <div class="card-body p-4 sm:p-5 space-y-3">
                            <h3 class="font-bold text-sm text-primary flex items-center gap-1.5">
                                <span class="size-2 rounded-full bg-primary inline-block"></span>
                                Posisi & Tipografi Nama Gugusdepan
                            </h3>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Posisi X (%)</span></label>
                                    <input type="number" step="0.1" min="0" max="100" wire:model.live="t_name_x" class="input input-sm input-bordered w-full font-mono text-xs" />
                                </div>
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Posisi Y (%)</span></label>
                                    <input type="number" step="0.1" min="0" max="100" wire:model.live="t_name_y" class="input input-sm input-bordered w-full font-mono text-xs" />
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Ukuran Font</span></label>
                                    <input type="number" min="8" max="72" wire:model.live="t_name_size" class="input input-sm input-bordered w-full text-xs" />
                                </div>
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Perataan</span></label>
                                    <select wire:model.live="t_name_align" class="select select-sm select-bordered w-full text-xs">
                                        <option value="center">Tengah</option>
                                        <option value="left">Kiri</option>
                                        <option value="right">Kanan</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Warna</span></label>
                                    <div class="flex items-center gap-1">
                                        <input type="color" wire:model.live="t_name_color" class="size-8 rounded cursor-pointer border border-base-300 p-0.5" />
                                        <input type="text" wire:model.live="t_name_color" class="input input-sm input-bordered w-full font-mono text-[11px] px-1" />
                                    </div>
                                </div>
                            </div>

                            <label class="label cursor-pointer justify-start gap-2 pt-1 pb-0">
                                <input type="checkbox" wire:model.live="t_name_bold" class="checkbox checkbox-primary checkbox-xs rounded" />
                                <span class="label-text text-xs font-semibold">Tebal (Bold)</span>
                            </label>
                        </div>
                    </div>

                    {{-- Card Posisi Nomor Gugusdepan --}}
                    <div class="card bg-base-100 shadow-soft border border-base-200">
                        <div class="card-body p-4 sm:p-5 space-y-3">
                            <h3 class="font-bold text-sm text-secondary flex items-center gap-1.5">
                                <span class="size-2 rounded-full bg-secondary inline-block"></span>
                                Posisi & Tipografi Nomor Gugusdepan
                            </h3>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Posisi X (%)</span></label>
                                    <input type="number" step="0.1" min="0" max="100" wire:model.live="t_number_x" class="input input-sm input-bordered w-full font-mono text-xs" />
                                </div>
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Posisi Y (%)</span></label>
                                    <input type="number" step="0.1" min="0" max="100" wire:model.live="t_number_y" class="input input-sm input-bordered w-full font-mono text-xs" />
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Ukuran Font</span></label>
                                    <input type="number" min="8" max="72" wire:model.live="t_number_size" class="input input-sm input-bordered w-full text-xs" />
                                </div>
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Perataan</span></label>
                                    <select wire:model.live="t_number_align" class="select select-sm select-bordered w-full text-xs">
                                        <option value="center">Tengah</option>
                                        <option value="left">Kiri</option>
                                        <option value="right">Kanan</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="label py-1"><span class="label-text text-xs">Warna</span></label>
                                    <div class="flex items-center gap-1">
                                        <input type="color" wire:model.live="t_number_color" class="size-8 rounded cursor-pointer border border-base-300 p-0.5" />
                                        <input type="text" wire:model.live="t_number_color" class="input input-sm input-bordered w-full font-mono text-[11px] px-1" />
                                    </div>
                                </div>
                            </div>

                            <label class="label cursor-pointer justify-start gap-2 pt-1 pb-0">
                                <input type="checkbox" wire:model.live="t_number_bold" class="checkbox checkbox-secondary checkbox-xs rounded" />
                                <span class="label-text text-xs font-semibold">Tebal (Bold)</span>
                            </label>
                        </div>
                    </div>

                    {{-- Tombol Aksi Simpan & Reset --}}
                    <div class="flex flex-col gap-2 pt-2">
                        <button type="submit" class="btn btn-primary w-full gap-2 font-bold shadow-md">
                            <x-ui.icon name="check" class="size-5" />
                            Simpan Perubahan Sertifikat Gugusdepan
                        </button>

                        <div class="grid grid-cols-2 gap-2">
                            <button 
                                type="button" 
                                wire:click="resetTroopLayout" 
                                class="btn btn-outline btn-sm gap-1.5"
                            >
                                <x-ui.icon name="arrow-path" class="size-4" />
                                Reset Posisi
                            </button>

                            <a 
                                href="{{ route('admin.certificates.preview', 'troop') }}" 
                                target="_blank" 
                                class="btn btn-ghost btn-sm gap-1.5"
                            >
                                <x-ui.icon name="eye" class="size-4" />
                                Preview PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>
