{{-- Modal konfirmasi global (pengganti confirm() browser); dipicu atribut x-confirm --}}
<div
    x-data
    x-init="$store.confirm.defaults = @js(['title' => __('Are you sure?'), 'confirm' => __('Yes'), 'cancel' => __('Cancel')])"
    x-effect="$store.confirm.open ? $refs.dialog.showModal() : $refs.dialog.close()"
    class="no-print"
>
    <dialog x-ref="dialog" class="modal" x-on:cancel.prevent="$store.confirm.answer(false)">
        <div class="modal-box max-w-md">
            <div class="flex items-start gap-4">
                <span
                    class="rounded-box grid size-11 shrink-0 place-items-center"
                    x-bind:class="{
                        'bg-error/15 text-error': $store.confirm.tone === 'error',
                        'bg-warning/20 text-warning': $store.confirm.tone === 'warning',
                        'bg-success/15 text-success': $store.confirm.tone === 'success',
                        'bg-primary/10 text-primary': $store.confirm.tone === 'primary',
                    }"
                >
                    <x-ui.icon name="exclamation" class="size-6" />
                </span>
                <div class="min-w-0">
                    <h3 class="text-lg font-semibold" x-text="$store.confirm.title"></h3>
                    <p class="text-base-content/70 mt-1 text-sm" x-text="$store.confirm.message"></p>
                </div>
            </div>

            <div class="modal-action mt-6">
                <button type="button" class="btn btn-ghost" x-on:click="$store.confirm.answer(false)" x-text="$store.confirm.cancel"></button>
                <button
                    type="button"
                    class="btn"
                    x-bind:class="{
                        'btn-error': $store.confirm.tone === 'error',
                        'btn-warning': $store.confirm.tone === 'warning',
                        'btn-success': $store.confirm.tone === 'success',
                        'btn-primary': $store.confirm.tone === 'primary',
                    }"
                    x-on:click="$store.confirm.answer(true)"
                    x-text="$store.confirm.confirm"
                ></button>
            </div>
        </div>

        <div class="modal-backdrop"><button type="button" x-on:click="$store.confirm.answer(false)">close</button></div>
    </dialog>
</div>
