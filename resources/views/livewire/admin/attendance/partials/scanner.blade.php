{{-- Panel scan QR absensi: kamera (qr-scanner), scanner USB/Bluetooth, atau ketik manual --}}
<div
    x-data="qrScanner({
        noCamera: @js(__('No camera found on this device.')),
        insecure: @js(__('Camera access needs HTTPS (or localhost). Use a USB scanner or type the ID instead.')),
        denied: @js(__('Camera permission was denied. Allow camera access in the browser and try again.')),
    })"
    x-on:scan-result.window="beep($event.detail.status)"
    class="space-y-4"
>
    <div wire:ignore class="space-y-2">
        <div class="bg-neutral rounded-box relative aspect-square w-full overflow-hidden" x-show="active" x-cloak>
            <video x-ref="video" class="size-full object-cover" muted playsinline></video>
        </div>

        <button type="button" class="btn btn-primary w-full" x-show="!active" x-on:click="start()">
            <x-ui.icon name="qr" class="size-4" /> {{ __('Scan QR with camera') }}
        </button>
        <button type="button" class="btn btn-outline w-full" x-show="active" x-cloak x-on:click="stop()">
            {{ __('Stop camera') }}
        </button>

        <p x-show="error" x-cloak x-text="error" class="text-error text-sm"></p>
    </div>

    <p class="text-base-content/55 pt-1 text-center text-xs">{{ __('or type / use a USB scanner') }}</p>

    <form wire:submit="checkIn" class="space-y-2">
        <x-ui.input :label="__('Participant ID')" name="code" wire:model="code" :placeholder="__('P-XXXXXXXX or KTA number')" autocomplete="off" autofocus />
        <button type="submit" class="btn btn-soft btn-primary w-full" wire:loading.attr="disabled" wire:target="checkIn">
            <x-ui.icon name="check" class="size-4" /> {{ __('Check in') }}
        </button>
    </form>

    @if ($lastCheckIn)
        @php
            // nama kelas lengkap (bukan hasil rangkaian) agar terdeteksi Tailwind
            $tone = [
                'success' => 'bg-success/10',
                'duplicate' => 'bg-warning/15',
                'error' => 'bg-error/10',
            ][$lastCheckIn['status']];
        @endphp
        <div class="rounded-box {{ $tone }} space-y-1 p-3 text-sm" wire:key="scan-{{ $lastCheckIn['code'] ?? 'error' }}-{{ $lastCheckIn['time'] ?? '' }}">
            @if ($lastCheckIn['status'] === 'error')
                <p class="text-error flex items-center gap-2 font-semibold">
                    <x-ui.icon name="x-circle" class="size-5" /> {{ __('Not recorded') }}
                </p>
                <p>{{ $lastCheckIn['message'] }}</p>
            @else
                <p @class(['flex items-center gap-2 font-semibold', 'text-success' => $lastCheckIn['status'] === 'success', 'text-warning' => $lastCheckIn['status'] === 'duplicate'])>
                    <x-ui.icon :name="$lastCheckIn['status'] === 'success' ? 'check-circle' : 'exclamation'" class="size-5" />
                    {{ $lastCheckIn['status'] === 'success' ? __('Checked in') : __('Already checked in') }}
                </p>
                <p class="text-base font-semibold">{{ $lastCheckIn['name'] }}</p>
                <p class="text-base-content/70 font-mono text-xs">{{ $lastCheckIn['code'] }} · {{ $lastCheckIn['kta'] }}</p>
                <p class="text-base-content/70 text-xs">{{ $lastCheckIn['troop'] }}</p>
                <p class="flex items-center gap-2 pt-1">
                    <x-ui.group-badge :number="$lastCheckIn['group']" :label="true" size="lg" />
                    <span class="text-base-content/60 text-xs">{{ $lastCheckIn['time'] }}</span>
                </p>
            @endif
        </div>
    @endif
</div>
