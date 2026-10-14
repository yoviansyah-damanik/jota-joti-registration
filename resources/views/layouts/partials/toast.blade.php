{{-- Notifikasi global: dipicu lewat $this->dispatch('toast', message: '...', type: 'success') --}}
<div
    x-data="{
        toasts: [],
        push(detail) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message: detail.message, type: detail.type ?? 'success' });
            setTimeout(() => this.toasts = this.toasts.filter(t => t.id !== id), 4000);
        }
    }"
    x-on:toast.window="push($event.detail)"
    x-init="@if (session('toast')) push(@js(session('toast'))) @endif"
    class="toast toast-top toast-end z-50 no-print"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div class="alert shadow-lg" :class="'alert-' + toast.type">
            <span x-text="toast.message"></span>
        </div>
    </template>
</div>
