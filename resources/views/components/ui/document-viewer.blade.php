{{-- Modal pratinjau dokumen (gambar/PDF); dipakai di dalam elemen ber-x-data="documentViewer" --}}
<dialog x-ref="dialog" class="modal" x-on:close="closed()">
    <div class="modal-box flex h-[90vh] w-11/12 max-w-5xl flex-col gap-3 p-4 sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h3 class="truncate text-base font-semibold" x-text="doc.title"></h3>
                <p class="text-base-content/60 truncate text-xs" x-text="doc.name"></p>
            </div>
            <button type="button" class="btn btn-ghost btn-sm btn-square" x-on:click="close()" aria-label="{{ __('Close') }}">✕</button>
        </div>

        <div class="bg-base-200 rounded-box relative min-h-0 flex-1 overflow-hidden">
            <div x-show="loading" class="absolute inset-0 grid place-items-center">
                <span class="loading loading-spinner loading-lg text-primary"></span>
            </div>

            <template x-if="doc.kind === 'image'">
                <img x-bind:src="doc.url" x-bind:alt="doc.title" x-on:load="loading = false" class="size-full object-contain">
            </template>
            <template x-if="doc.kind === 'pdf'">
                <iframe x-bind:src="doc.url" x-bind:title="doc.title" x-on:load="loading = false" class="size-full border-0"></iframe>
            </template>
        </div>

        <div class="modal-action mt-0">
            <a x-bind:href="doc.url" target="_blank" class="btn btn-ghost btn-sm">{{ __('Open in new tab') }}</a>
            <a x-bind:href="doc.download" class="btn btn-primary btn-sm">
                <x-ui.icon name="download" class="size-4" /> {{ __('Download') }}
            </a>
        </div>
    </div>

    <form method="dialog" class="modal-backdrop"><button type="button" x-on:click="close()">{{ __('Close') }}</button></form>
</dialog>
