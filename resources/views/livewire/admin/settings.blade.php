<div>
    <x-ui.page-header :title="__('Settings')" :subtitle="__('Registration and group number configuration')" />

    <form wire:submit="save" class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="space-y-6">
            <x-ui.card :title="__('Registration')" icon="flag">
                <x-ui.input :label="__('Event name')" name="event_name" wire:model="event_name" required />
                <x-ui.input :label="__('Organization name')" name="organization_name" wire:model="organization_name" required />

                <label class="label cursor-pointer gap-2 py-2">
                    <input type="checkbox" wire:model="registration_open" class="toggle toggle-primary" />
                    <span class="text-base-content">{{ __('Registration is open') }}</span>
                </label>

                <x-ui.input :label="__('Registration fee per participant (Rp)')" name="registration_fee" type="number" min="0" wire:model="registration_fee" :hint="__('Total = number of participants × this fee.')" />
                <x-ui.textarea :label="__('Bank account info')" name="bank_account_info" wire:model="bank_account_info" rows="3" />
                <x-ui.input :label="__('Contact info')" name="contact_info" wire:model="contact_info" />
                <x-ui.textarea :label="__('Other terms')" name="additional_terms" wire:model="additional_terms" rows="5" :hint="__('One term per line. Shown on the requirements page.')" />
            </x-ui.card>

            <x-ui.card :title="__('Pengiriman Tugas')" icon="document">
                <label class="label cursor-pointer justify-start gap-2 py-2">
                    <input type="checkbox" wire:model="task_submission_enabled" class="toggle toggle-primary" />
                    <span class="text-base-content font-semibold">{{ __('Aktifkan Pengiriman Tugas') }}</span>
                </label>
                <p class="text-base-content/60 -mt-1 mb-3 text-xs">
                    {{ __('Jika dinonaktifkan, formulir pengiriman tugas (/tugas) akan ditutup secara manual.') }}
                </p>

                <div class="grid gap-4 sm:grid-cols-2 pt-1">
                    <div>
                        <label class="label pb-1" for="task_submission_open_at">
                            <span class="label-text font-semibold text-xs">{{ __('Batas Waktu Buka (Mulai)') }}</span>
                        </label>
                        <input type="datetime-local" id="task_submission_open_at" wire:model="task_submission_open_at"
                            class="input input-bordered w-full text-xs @error('task_submission_open_at') input-error @enderror" />
                        <p class="text-[11px] text-base-content/50 mt-1">{{ __('Kosongkan jika dibuka langsung tanpa jadwal.') }}</p>
                        @error('task_submission_open_at')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label pb-1" for="task_submission_close_at">
                            <span class="label-text font-semibold text-xs">{{ __('Batas Waktu Tutup (Selesai)') }}</span>
                        </label>
                        <input type="datetime-local" id="task_submission_close_at" wire:model="task_submission_close_at"
                            class="input input-bordered w-full text-xs @error('task_submission_close_at') input-error @enderror" />
                        <p class="text-[11px] text-base-content/50 mt-1">{{ __('Kosongkan jika tidak ada batas waktu tutup otomatis.') }}</p>
                        @error('task_submission_close_at')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card :title="__('Official Logo')" icon="sparkles">
                <div class="flex flex-col items-center gap-5 sm:flex-row">
                    <div class="bg-base-200/60 border-base-300 flex size-28 shrink-0 items-center justify-center rounded-2xl border p-3 shadow-xs">
                        @if ($logo)
                            <img src="{{ $logo->temporaryUrl() }}" alt="Preview" class="max-h-full max-w-full object-contain" />
                        @else
                            <img src="{{ $currentLogoUrl }}" alt="Official Logo" class="max-h-full max-w-full object-contain" />
                        @endif
                    </div>

                    <div class="flex-1 space-y-2 text-center sm:text-left">
                        <p class="text-sm font-semibold">{{ __('Upload official JOTA-JOTI logo') }}</p>
                        <p class="text-base-content/60 text-xs">
                            {{ __('Supported formats: PNG, JPG, WebP, SVG. Max 2 MB. This logo will appear on public pages, cards, and certificates.') }}
                        </p>

                        <div class="flex flex-wrap items-center justify-center gap-2 pt-1 sm:justify-start">
                            <label class="btn btn-sm btn-outline btn-primary cursor-pointer">
                                <x-ui.icon name="upload" class="size-4" />
                                <span>{{ __('Choose Logo') }}</span>
                                <input type="file" wire:model="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="hidden" />
                            </label>

                            @if ($hasCustomLogo)
                                <button type="button" wire:click="deleteLogo" class="btn btn-sm btn-ghost text-error"
                                    x-confirm="{ message: @js(__('Reset official logo to default?')), tone: 'error', confirm: @js(__('Reset Logo')) }">
                                    <x-ui.icon name="trash" class="size-4" /> {{ __('Reset to Default') }}
                                </button>
                            @endif
                        </div>

                        <div wire:loading wire:target="logo" class="text-primary flex items-center gap-1.5 text-xs font-medium">
                            <span class="loading loading-spinner loading-xs"></span> {{ __('Uploading logo preview...') }}
                        </div>
                        @error('logo') <p class="text-error text-xs">{{ $message }}</p> @enderror
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card :title="__('Group numbers & Unit Blocking')" icon="user-group">
                <p class="text-base-content/70 -mt-2 mb-4 text-xs">
                    {{ __('Set separate group ranges for Penggalang and Penegak/Pandega. Members of different units are never mixed into the same group.') }}
                </p>

                <div class="space-y-4">
                    {{-- Penggalang Block --}}
                    <div class="rounded-xl border border-sky-500/20 bg-sky-500/5 p-4">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="badge badge-info badge-sm font-semibold uppercase tracking-wider">
                                {{ __('Penggalang Block') }}
                            </span>
                            <span class="text-xs font-semibold text-sky-700 dark:text-sky-300">
                                {{ $this->penggalangRange() }}
                            </span>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <x-ui.input :label="__('Number of groups')" name="penggalang_group_count" type="number" min="1" max="50" wire:model.live="penggalang_group_count" required />
                            <x-ui.input :label="__('Starting group number')" name="penggalang_start_number" type="number" min="1" max="100" wire:model.live="penggalang_start_number" required />
                        </div>
                    </div>

                    {{-- Penegak/Pandega Block --}}
                    <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/5 p-4">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="badge badge-success badge-sm font-semibold uppercase tracking-wider">
                                {{ __('Penegak / Pandega Block') }}
                            </span>
                            <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                                {{ $this->penegakRange() }}
                            </span>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <x-ui.input :label="__('Number of groups')" name="penegak_group_count" type="number" min="1" max="50" wire:model.live="penegak_group_count" required />
                            <x-ui.input :label="__('Starting group number (optional)')" name="penegak_start_number" type="number" min="1" max="100" wire:model.live="penegak_start_number" :hint="__('Leave blank to auto-continue after Penggalang.')" />
                        </div>
                    </div>

                    {{-- Distribution Rule --}}
                    <div>
                        <x-ui.input :label="__('Minimum per number per troop')" name="min_per_number" type="number" min="0" max="20" wire:model.live="min_per_number" :hint="__('Each troop is distributed evenly across its unit group block.')" />
                    </div>
                </div>

                <x-ui.alert type="info" class="mt-4 text-xs">
                    {{ __('Penggalang troops require at least :pcount participants (:pgroups × minimum). Penegak troops require at least :tcount participants (:tgroups × minimum).', [
                        'pcount' => (int) $penggalang_group_count * (int) $min_per_number,
                        'pgroups' => (int) $penggalang_group_count,
                        'tcount' => (int) $penegak_group_count * (int) $min_per_number,
                        'tgroups' => (int) $penegak_group_count,
                    ]) }}
                    <br>{{ __('After changing group counts or ranges, re-run the group assignment with reset.') }}
                </x-ui.alert>
            </x-ui.card>

            <x-ui.card :title="__('Certificates')" icon="document">
                <label class="label cursor-pointer justify-start gap-2 py-2">
                    <input type="checkbox" wire:model="event_completed" class="toggle toggle-success" />
                    <span class="text-base-content">{{ __('Event is completed') }}</span>
                </label>
                <p class="text-base-content/60 -mt-1 mb-2 text-xs">{{ __('When enabled, verified troops can download a troop certificate and certificates for participants who attended at least one session.') }}</p>

                <x-ui.input :label="__('Signer name')" name="certificate_signer_name" wire:model="certificate_signer_name" />
                <x-ui.input :label="__('Signer title')" name="certificate_signer_title" wire:model="certificate_signer_title" />
                <x-ui.input :label="__('Certificate city')" name="certificate_city" wire:model="certificate_city" />
            </x-ui.card>

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                    {{ __('Save settings') }}
                </button>
            </div>
        </div>
    </form>
</div>
