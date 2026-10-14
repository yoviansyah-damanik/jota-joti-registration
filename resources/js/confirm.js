// Pengganti confirm() bawaan browser: modal daisyUI yang dipakai lewat atribut x-confirm.
// Contoh: <button wire:click="delete" x-confirm="{ message: '...', tone: 'error', confirm: 'Hapus' }">
export default function registerConfirm(Alpine) {
    Alpine.store('confirm', {
        open: false,
        title: '',
        message: '',
        confirm: '',
        cancel: '',
        tone: 'primary',
        defaults: { title: 'Are you sure?', confirm: 'Yes', cancel: 'Cancel' },
        resolver: null,

        ask(options) {
            const config = typeof options === 'string' ? { message: options } : options ?? {};

            return new Promise((resolve) => {
                this.resolver?.(false);
                this.resolver = resolve;
                this.title = config.title ?? this.defaults.title;
                this.message = config.message ?? '';
                this.confirm = config.confirm ?? this.defaults.confirm;
                this.cancel = config.cancel ?? this.defaults.cancel;
                this.tone = config.tone ?? 'primary';
                this.open = true;
            });
        },

        answer(ok) {
            this.open = false;
            this.resolver?.(ok);
            this.resolver = null;
        },
    });

    // Livewire (wire:click) memanggil el.__livewire_confirm(lanjut, batal) sebelum menjalankan aksi
    Alpine.directive('confirm', (el, { expression }, { evaluate, cleanup }) => {
        el.__livewire_confirm = (proceed, abort) => {
            Alpine.store('confirm')
                .ask(evaluate(expression))
                .then((ok) => (ok ? proceed() : abort()));
        };

        cleanup(() => delete el.__livewire_confirm);
    });
}
